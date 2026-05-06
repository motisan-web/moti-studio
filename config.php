<?php
define('DATA_DIR',      __DIR__ . '/data');
define('DATA_POSTS',    DATA_DIR . '/posts');
define('DATA_EVALS',    DATA_DIR . '/evals');
define('DATA_AUTH',     DATA_DIR . '/auth');
define('DATA_ACCOUNTS', DATA_DIR . '/accounts');
define('DATA_DRAFTS',   DATA_DIR . '/drafts');
define('DATA_AVATARS',  DATA_DIR . '/avatars');

// 同期設定（値は環境変数または .env.local で上書き可）
define('SYNC_KEY',      getenv('SYNC_KEY')      ?: 'change-me');
define('SYNC_ENDPOINT', getenv('SYNC_ENDPOINT') ?: 'https://d00e.motisan.info/api/sync');
