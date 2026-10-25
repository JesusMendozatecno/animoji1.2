<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use Illuminate\Database\Seeder;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── ANIME (imágenes de prueba en public/images/anime/) ─────────
        $animes = [
            ['slug' => 'one-piece', 'title' => 'One Piece', 'synopsis' => 'Monkey D. Luffy y su tripulación buscan el legendario tesoro One Piece.', 'studio' => 'Toei Animation', 'year' => 1999, 'status' => 'ongoing', 'rating' => 9.1],
            ['slug' => 'kimetsu-no-yaiba', 'title' => 'Kimetsu no Yaiba', 'synopsis' => 'Tanjirou Kamado se une al Cazadores de Demonios para vengar a su familia.', 'studio' => 'ufotable', 'year' => 2019, 'status' => 'ongoing', 'rating' => 8.7],
            ['slug' => 'jujutsu-kaisen', 'title' => 'Jujutsu Kaisen', 'synopsis' => 'Yuji Itadori traga un dedo maldito y se convierte en huésped de Ryomen Sukuna.', 'studio' => 'MAPPA', 'year' => 2020, 'status' => 'ongoing', 'rating' => 8.8],
            ['slug' => 'solo-leveling', 'title' => 'Solo Leveling', 'synopsis' => 'Sung Jin-Woo, un cazador de rango E, descubre un sistema que le permite subir de nivel sin límites.', 'studio' => 'A-1 Pictures', 'year' => 2024, 'status' => 'ongoing', 'rating' => 9.0],
            ['slug' => 'naruto-shippuden', 'title' => 'Naruto Shippuden', 'synopsis' => 'Naruto regresa tras dos años de entrenamiento para rescatar a Sasuke.', 'studio' => 'Studio Pierrot', 'year' => 2007, 'status' => 'completed', 'rating' => 8.9],
            ['slug' => 'attack-on-titan', 'title' => 'Attack on Titan', 'synopsis' => 'La humanidad lucha por sobrevivir tras vivir tras murallas frente a los titanes.', 'studio' => 'Wit / MAPPA', 'year' => 2013, 'status' => 'completed', 'rating' => 9.0],
            ['slug' => 'tokyo-revengers', 'title' => 'Tokyo Revengers', 'synopsis' => 'Takemichi viaja en el tiempo para salvar a sus amigos del grupo delincuente Toman.', 'studio' => 'Liden Films', 'year' => 2021, 'status' => 'completed', 'rating' => 8.2],
            ['slug' => 'demon-slayer-mugen-train', 'title' => 'Demon Slayer: Mugen Train', 'synopsis' => 'Tanjiro y sus compañeros investigan una serie de desapariciones en el Tren Infinito.', 'studio' => 'ufotable', 'year' => 2020, 'status' => 'completed', 'rating' => 8.6],
        ];
        foreach ($animes as $i => $a) {
            Anime::query()->updateOrCreate(['slug' => $a['slug']], $a + ['cover' => '/images/anime/cover-'.($i + 1).'.jpg']);
        }

        // ── MANGA (imágenes de prueba en public/images/manga/) ──────────
        $mangas = [
            ['slug' => 'one-piece-manga', 'title' => 'One Piece', 'synopsis' => 'La aventura de Luffy por el Grand Line en busca del tesoro One Piece.', 'author' => 'Eiichiro Oda', 'artist' => 'Eiichiro Oda', 'year' => 1997, 'status' => 'ongoing', 'rating' => 9.6],
            ['slug' => 'kimetsu-no-yaiba-manga', 'title' => 'Kimetsu no Yaiba', 'synopsis' => 'Un niño se convierte en cazador de demonios tras la tragedia de su familia.', 'author' => 'Koyoharu Gotouge', 'artist' => 'Koyoharu Gotouge', 'year' => 2016, 'status' => 'completed', 'rating' => 9.3],
            ['slug' => 'jujutsu-kaisen-manga', 'title' => 'Jujutsu Kaisen', 'synopsis' => 'Hechizos, maldiciones y combates en el mundo sobrenatural del instituto Jujutsu.', 'author' => 'Gege Akutami', 'artist' => 'Gege Akutami', 'year' => 2018, 'status' => 'ongoing', 'rating' => 9.4],
            ['slug' => 'solo-leveling-manga', 'title' => 'Solo Leveling', 'synopsis' => 'El cazador más débil obtiene el poder de subir de nivel sin límite.', 'author' => 'Chugong', 'artist' => 'Dubu', 'year' => 2018, 'status' => 'completed', 'rating' => 9.2],
            ['slug' => 'naruto-manga', 'title' => 'Naruto', 'synopsis' => 'El ninja más ruidoso de la Aldea Oculta persigue su sueño de ser Hokage.', 'author' => 'Masashi Kishimoto', 'artist' => 'Masashi Kishimoto', 'year' => 1999, 'status' => 'completed', 'rating' => 8.9],
            ['slug' => 'attack-on-titan-manga', 'title' => 'Attack on Titan', 'synopsis' => 'La humanidad se refugia tras enormes murallas frente a los titanes.', 'author' => 'Hajime Isayama', 'artist' => 'Hajime Isayama', 'year' => 2009, 'status' => 'completed', 'rating' => 9.1],
            ['slug' => 'tokyo-revengers-manga', 'title' => 'Tokyo Revengers', 'synopsis' => 'Un pandillero viaja en el tiempo para cambiar su destino y el de sus amigos.', 'author' => 'Ken Wakui', 'artist' => 'Ken Wakui', 'year' => 2017, 'status' => 'completed', 'rating' => 8.6],
            ['slug' => 'demon-slayer-manga', 'title' => 'Demon Slayer: Kimetsu no Yaiba', 'synopsis' => 'La lucha de Tanjiro contra los demonios para salvar a su hermana Nezuko.', 'author' => 'Koyoharu Gotouge', 'artist' => 'Koyoharu Gotouge', 'year' => 2016, 'status' => 'completed', 'rating' => 8.8],
        ];
        foreach ($mangas as $i => $m) {
            Manga::query()->updateOrCreate(['slug' => $m['slug']], $m + ['cover' => '/images/manga/cover-'.($i + 1).'.jpg']);
        }

        // ── MÚSICA (imágenes de prueba en public/images/musica/) ────────
        $tracks = [
            ['slug' => 'hatsune-miku-miku', 'title' => 'Hatsune Miku - Miku', 'artist' => 'Hatsune Miku', 'album' => 'Vocaloid Essentials', 'duration' => 222, 'release_year' => 2007],
            ['slug' => 'kick-back', 'title' => 'KICK BACK', 'artist' => 'Kenshi Yonezu', 'album' => 'KICK BACK', 'duration' => 193, 'release_year' => 2022],
            ['slug' => 'yoru-ni-akeru', 'title' => '夜に駆ける (Yoru ni Akeru)', 'artist' => 'YOASOBI', 'album' => 'THE BOOK', 'duration' => 261, 'release_year' => 2019],
            ['slug' => 'mixed-nuts', 'title' => 'Mixed Nuts', 'artist' => 'Official HIGE DANdism', 'album' => 'Mixed Nuts', 'duration' => 242, 'release_year' => 2022],
            ['slug' => 'kaikai-kitan', 'title' => 'Kaikai Kitan', 'artist' => 'Eve', 'album' => 'Kaikai Kitan', 'duration' => 231, 'release_year' => 2020],
            ['slug' => 'the-rumbling', 'title' => 'The Rumbling', 'artist' => 'SiM', 'album' => 'The Rumbling', 'duration' => 206, 'release_year' => 2022],
            ['slug' => 'again', 'title' => 'Again', 'artist' => 'YUI', 'album' => 'Again', 'duration' => 257, 'release_year' => 2009],
            ['slug' => 'alone', 'title' => 'Alone', 'artist' => 'Alan Walker', 'album' => 'Different World', 'duration' => 161, 'release_year' => 2018],
        ];
        foreach ($tracks as $i => $t) {
            MusicTrack::query()->updateOrCreate(['slug' => $t['slug']], $t + ['cover' => '/images/musica/cover-'.($i + 1).'.jpg']);
        }

        // ── JUEGOS ──────────────────────────────────────────────────────
        Game::query()->updateOrCreate(['slug' => 'genshin-impact'], [
            'title' => 'Genshin Impact',
            'description' => 'RPG de mundo abierto en el continente de Teyvat.',
            'platforms' => ['PC', 'PlayStation 5', 'Android', 'iOS'],
            'requirements' => 'Windows 10, Intel Core i5, 8 GB RAM, GTX 1030.',
            'download_url' => 'https://genshin.hoyoverse.com',
            'release_year' => 2020,
            'rating' => 4.5,
        ]);

        Game::query()->updateOrCreate(['slug' => 'elden-ring'], [
            'title' => 'Elden Ring',
            'description' => 'Action RPG de mundo abierto creado por FromSoftware y George R. R. Martin.',
            'platforms' => ['PC', 'PlayStation 5', 'Xbox Series X|S'],
            'requirements' => 'Windows 10, Intel Core i5-8400, 12 GB RAM, GTX 1060.',
            'download_url' => 'https://en.bandainamcoent.eu/elden-ring/',
            'release_year' => 2022,
            'rating' => 4.8,
        ]);
    }
}
