<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Skinrave applicants with countdown data
echo json_encode([
    'totalCount' => 10,
    'filteredCount' => 10,
    'endsAt' => date('c', strtotime('+7 days')),
    'list' => [
        ['user' => ['id' => 1, 'username' => 'sss', 'level' => 6, 'levelTier' => 'IRON', 'avatarUrl' => 'https://cdn.discordapp.com/avatars/800759672327766036/474d20201279af2d35c15b4ab2cf76a5.png'], 'wagered' => '71.41'],
        ['user' => ['id' => 2, 'username' => 'chud', 'level' => 2, 'levelTier' => 'IRON', 'avatarUrl' => 'https://static.skinrave.gg/skinrave-front-assets/avatars/15.webp'], 'wagered' => '43.72'],
        ['user' => ['id' => 3, 'username' => 'Vivi Nikke', 'level' => 4, 'levelTier' => 'IRON', 'avatarUrl' => 'https://lh3.googleusercontent.com/a/ACg8ocIbqyjVgLjHCRpzbq2KM7XXb9-xCKFYW25ofgtq9Q2W7hqjsw=s96-c'], 'wagered' => '38.54'],
        ['user' => ['id' => 4, 'username' => 'Ragnar', 'level' => 1, 'levelTier' => 'IRON', 'avatarUrl' => 'https://avatars.steamstatic.com/413fea8ad5957d0437adb29e0552679672c56037_full.jpg'], 'wagered' => '33.34'],
        ['user' => ['id' => 5, 'username' => 'Remuko', 'level' => 25, 'levelTier' => 'SILVER', 'avatarUrl' => 'https://avatars.steamstatic.com/97c9f0cd60d9a0178bfec9b88c3edfededcbc14d_full.jpg'], 'wagered' => '8.89'],
        ['user' => ['id' => 6, 'username' => 'dexteR', 'level' => 3, 'levelTier' => 'IRON', 'avatarUrl' => 'https://avatars.steamstatic.com/c9a9aae83a786b31825330aa67ab5af9e468cde6_full.jpg'], 'wagered' => '7.44'],
        ['user' => ['id' => 7, 'username' => 'DWMTM', 'level' => 1, 'levelTier' => 'IRON', 'avatarUrl' => 'https://avatars.steamstatic.com/35b129ea91f31553983cb2e331ac90e7cd23d484_full.jpg'], 'wagered' => '2.56'],
        ['user' => ['id' => 8, 'username' => 'Duckie', 'level' => 2, 'levelTier' => 'IRON', 'avatarUrl' => 'https://avatars.steamstatic.com/ba03d6d44484a0df67b1b7e60ac82b51b68cd37c_full.jpg'], 'wagered' => '0.18'],
        ['user' => ['id' => 9, 'username' => 'soccerfish37', 'level' => 1, 'levelTier' => 'IRON', 'avatarUrl' => 'https://avatars.steamstatic.com/754f4f657699e2166ececf0e411366343fe87e96_full.jpg'], 'wagered' => '0.00'],
        ['user' => ['id' => 10, 'username' => 'Liam Shorts', 'level' => 1, 'levelTier' => 'IRON', 'avatarUrl' => 'https://lh3.googleusercontent.com/a/ACg8ocLxhF2K-h6Q3jAPDu_bjP2Ns_TgAPaQXrZmdt-HMMYgGncLEA=s96-c'], 'wagered' => '0.00']
    ]
]);



