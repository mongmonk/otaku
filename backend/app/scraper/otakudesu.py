import httpx
import logging
import re
from selectolax.lexbor import LexborHTMLParser
from typing import List, Dict, Any, Optional
import asyncio

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class OtakuDesuScraper:
    def __init__(self):
        self.base_url = "https://otakudesu.best"
        self.headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
        }

    async def _fetch(self, url: str) -> Optional[str]:
        try:
            async with httpx.AsyncClient(headers=self.headers, follow_redirects=True, timeout=20.0) as client:
                response = await client.get(url)
                response.raise_for_status()
                return response.text
        except Exception as e:
            logger.error(f"Error fetching {url}: {e}")
            return None

    async def crawl_ongoing(self) -> List[Dict[str, Any]]:
        url = f"{self.base_url}/ongoing-anime/"
        html = await self._fetch(url)
        if not html:
            return []

        parser = LexborHTMLParser(html)
        anime_list = []
        
        # Mencari elemen anime dalam list ongoing
        # Berdasarkan struktur umum OtakuDesu: div.venutama -> div.detpost
        nodes = parser.css("div.venutama div.venul ul li, div.venutama div.detpost")
        
        for node in nodes:
            try:
                title_node = node.css_first("h2") or node.css_first("div.jdlflm")
                link_node = node.css_first("a")
                thumb_node = node.css_first("img")
                
                if title_node and link_node:
                    anime_url = link_node.attributes.get("href")
                    # Slug biasanya bagian terakhir dari URL
                    slug = anime_url.strip("/").split("/")[-1] if anime_url else ""
                    
                    anime_list.append({
                        "title": title_node.text().strip(),
                        "slug": slug,
                        "url": anime_url,
                        "poster_url": thumb_node.attributes.get("src") if thumb_node else None,
                        "last_episode": node.css_first("div.epz").text().strip() if node.css_first("div.epz") else None,
                    })
            except Exception as e:
                logger.error(f"Error parsing ongoing item: {e}")
                continue
                
        return anime_list

    async def crawl_sitemap(self, sitemap_urls: List[str]) -> List[Dict[str, Any]]:
        """
        Mengambil daftar URL anime dari file XML sitemap.
        """
        anime_list = []
        for url in sitemap_urls:
            logger.info(f"Crawling sitemap: {url}")
            xml = await self._fetch(url)
            if not xml:
                continue
            
            # Ekstraksi URL menggunakan regex sederhana
            urls = re.findall(r'<loc>(.*?)</loc>', xml)
            for anime_url in urls:
                # Filter hanya URL anime, hindari URL sitemap itu sendiri atau halaman kategori
                if "/anime/" in anime_url and not anime_url.endswith("/anime/"):
                    slug = anime_url.strip("/").split("/")[-1]
                    # Kita gunakan slug sebagai placeholder title jika belum ada
                    anime_list.append({
                        "url": anime_url,
                        "slug": slug,
                        "title": slug.replace("-", " ").title()
                    })
        
        # Hapus duplikat berdasarkan URL
        unique_anime = {a['url']: a for a in anime_list}.values()
        logger.info(f"Total anime unik ditemukan di sitemap: {len(unique_anime)}")
        return list(unique_anime)

    async def get_anime_detail(self, url: str) -> Optional[Dict[str, Any]]:
        html = await self._fetch(url)
        if not html:
            return None

        parser = LexborHTMLParser(html)
        try:
            # Info detail biasanya ada di div.fotoanime dan div.infozin
            info_node = parser.css_first("div.infozin")
            if not info_node:
                return None

            info_dict = {}
            for p in info_node.css("p"):
                text = p.text().strip()
                if ":" in text:
                    key, val = text.split(":", 1)
                    info_dict[key.strip().lower()] = val.strip()

            # Mapping ke schema database
            # Judul: info_dict.get('judul')
            # Skor: info_dict.get('skor')
            # Produser: info_dict.get('produser')
            # Tipe: info_dict.get('tipe')
            # Status: info_dict.get('status')
            # Total Episode: info_dict.get('total episode')
            # Durasi: info_dict.get('durasi')
            # Tanggal Rilis: info_dict.get('tanggal rilis')
            # Studio: info_dict.get('studio')
            # Genre: info_dict.get('genre')

            slug = url.strip("/").split("/")[-1]
            
            # Sinopsis
            synopsis_node = parser.css_first("div.sinopc")
            synopsis = ""
            if synopsis_node:
                synopsis = "\n".join([p.text().strip() for p in synopsis_node.css("p")])

            # Poster
            poster_node = parser.css_first("div.fotoanime img")
            poster_url = poster_node.attributes.get("src") if poster_node else None

            # Episode List
            episodes = []
            # Daftar episode biasanya ada di div.episodelist
            episode_nodes = parser.css("div.episodelist ul li")
            for ep in episode_nodes:
                a_tag = ep.css_first("a")
                if a_tag:
                    ep_url = a_tag.attributes.get("href")
                    ep_title = a_tag.text().strip()
                    ep_slug = ep_url.strip("/").split("/")[-1] if ep_url else ""
                    
                    # Skip jika link bukan episode (misalnya link batch)
                    if "/episode/" in ep_url:
                        episodes.append({
                            "title": ep_title,
                            "episode_slug": ep_slug,
                            "url": ep_url,
                            "uploaded_at": ep.css_first("span.zeebr").text().strip() if ep.css_first("span.zeebr") else None
                        })

            return {
                "slug": slug,
                "title": info_dict.get("judul"),
                "title_jp": info_dict.get("japanese"),
                "score": float(info_dict.get("skor")) if info_dict.get("skor") and info_dict.get("skor").replace('.', '', 1).isdigit() else None,
                "producer": info_dict.get("produser"),
                "type": info_dict.get("tipe"),
                "status": info_dict.get("status"),
                "total_episode": int(''.join(filter(str.isdigit, info_dict.get("total episode", "0")))) if info_dict.get("total episode") and any(c.isdigit() for c in info_dict.get("total episode")) else 0,
                "duration": info_dict.get("durasi"),
                "release_date": info_dict.get("tanggal rilis"),
                "studio": info_dict.get("studio"),
                "genre": info_dict.get("genre"),
                "synopsis": synopsis,
                "poster_url": poster_url,
                "episodes": episodes
            }

        except Exception as e:
            logger.error(f"Error parsing detail for {url}: {e}")
            return None

    async def get_episode_links(self, url: str) -> Dict[str, Any]:
        """
        Ekstraksi link streaming dan download dari halaman episode.
        """
        html = await self._fetch(url)
        if not html:
            return {"stream_links": [], "download_links": []}

        parser = LexborHTMLParser(html)
        
        # 1. Ekstraksi Stream Links (Iframe Player)
        stream_links = []
        # Biasanya ada di div.mirrorstream ul li atau langsung di iframe
        mirror_nodes = parser.css("div.mirrorstream ul li")
        for node in mirror_nodes:
            try:
                # Kadang link stream ada di attribute data-content atau base64
                # Untuk saat ini kita ambil providernya
                provider = node.text().strip()
                # Link stream biasanya perlu di-resolve lagi atau diambil dari iframe
                # Namun di OtakuDesu seringkali menggunakan iframe src
                # Kita coba cari iframe di dalam post
                pass
            except:
                continue
        
        # Fallback: Ambil iframe utama jika ada
        iframe_node = parser.css_first("div.responsive-embed-stream iframe")
        if iframe_node:
            stream_links.append({
                "provider": "DesuDrive", # Default provider biasanya DesuDrive
                "url": iframe_node.attributes.get("src")
            })

        # 2. Ekstraksi Download Links
        download_links = []
        # Download links biasanya ada di div.download ul li
        # Strukturnya biasanya: <li><strong>Resolusi</strong> <a>Provider1</a> <a>Provider2</a></li>
        download_sections = parser.css("div.download ul li")
        for section in download_sections:
            try:
                res_node = section.css_first("strong")
                if not res_node:
                    continue
                
                resolution = res_node.text().strip()
                # Ambil semua link provider dalam li tersebut
                for a in section.css("a"):
                    provider = a.text().strip()
                    dl_url = a.attributes.get("href")
                    if dl_url:
                        download_links.append({
                            "resolution": resolution,
                            "provider": provider,
                            "url": dl_url
                        })
            except Exception as e:
                logger.error(f"Error parsing download links: {e}")
                continue

        return {
            "stream_links": stream_links,
            "download_links": download_links
        }

if __name__ == "__main__":
    # Test sederhana
    async def main():
        scraper = OtakuDesuScraper()
        print("Testing crawl_ongoing...")
        ongoing = await scraper.crawl_ongoing()
        print(f"Found {len(ongoing)} ongoing anime")
        
        if ongoing:
            print(f"Testing get_anime_detail for {ongoing[0]['url']}...")
            detail = await scraper.get_anime_detail(ongoing[0]['url'])
            if detail:
                print(f"Title: {detail['title']}")
                print(f"Episodes count: {len(detail['episodes'])}")
                
                if detail['episodes']:
                    print(f"Testing get_episode_links for {detail['episodes'][0]['url']}...")
                    links = await scraper.get_episode_links(detail['episodes'][0]['url'])
                    print(f"Stream links: {len(links['stream_links'])}")
                    print(f"Download links: {len(links['download_links'])}")
                    if links['download_links']:
                        print(f"Sample DL link: {links['download_links'][0]}")

    asyncio.run(main())