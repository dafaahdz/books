<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BooksSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        $nouns = [
            'Fate', 'Eclipse', 'Destiny', 'Rebellion', 'Empire',
            'Ashes', 'Prophecy', 'Vengeance', 'Silence', 'Oblivion',
            'Dawn', 'Dusk', 'Chaos', 'Harmony', 'Valor',
            'Ruin', 'Sanctuary', 'Myth', 'Oath', 'Betrayal',
            'Infinity', 'Abyss', 'Echoes', 'Realm', 'Dominion',
            'Specter', 'Embers', 'Tempest', 'Serenity', 'Cataclysm',
            'Awakening', 'Exile', 'Pact', 'Fury', 'Covenant',
            'Labyrinth', 'Oracle', 'Nemesis', 'Reckoning', 'Ascension', 'Mystery', 
            'Shadow', 'Curse', 'Legacy', 'Crown', 'Secrets', 'Wrath', 'Chronicles', 
            'Fall', 'Rise', 'Whispers', 'Night', 'Flame', 'Storm'
        ];

        $entities = [
            'Azkaban', 'Kinggre', 'Valoria', 'Eldryn', 'Morvane', 'Blackreach', 
            'Thornfell', 'Ravenhold', 'Ironspire', 'Ashkara', 'Velmora', 'Drakenmoor',
            'Nightfall', 'Grimholt', 'Stormvale', 'Dreadmoor', 'Frosthelm',
            'Shadowfen', 'Bloodreach', 'Ironfall', 'Voidspire', 'Darkwyn',
            'Ravencrest', 'Emberhold', 'Skullhaven', 'Ashenford', 'Blackspire',
            'Thundershade', 'Hexmoor', 'Duskryn', 'Gravewatch', 'Obsidian',
            'Valkareth', 'Morgrave', 'Fellwarden', 'Duskmire', 'Stormhold',
            'Netherfall', 'Cinderpeak', 'Ebonreach', 'Ironwatch', 'Ruinmark',
            'Grimspire', 'Frostmourne', 'Shadowhold', 'Nightspire', 'Bloodmoor',
            'Ashvale', 'Darkspire', 'Voidreach'
        ];

        $firstNames = [
            'Arwyn', 'Kaelen', 'Nyx', 'Elric', 'Seraphine',
            'Thalos', 'Iskandar', 'Lyra', 'Vaelis', 'Rowan'
        ];

        $lastNames = [
            'Darkmere', 'Stormveil', 'Blackthorn', 'Ironwood',
            'Nightfall', 'Ravenshade', 'Grimwald', 'Ashborne'
        ];

        $batchSize = 500;
        $totalData = 10000;

        $data = [];
        for($i = 1; $i <= $totalData; $i++) {
            $title = "The " . $nouns[array_rand($nouns)] .
                ' of ' . $entities[array_rand($entities)];

            $author = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)]; 
            $genreId = rand(1, 9);

            $data[] = [
                'title' => $title,
                'author' => $author,
                'genre_id' => $genreId,
                'price'      => rand(60000, 300000) / 100
            ];

            if (count($data) === $batchSize) {
                $db->table('books')->insertBatch($data);
                $data = [];
            }

            if (! empty($data)) {
                $db->table('books')->insertBatch($data);
            }
        }
    }
}
