<?php
if (php_sapi_name() !== 'cli') exit("CLIからのみ実行できます\n");

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/json.php';

$posts = [
  [
    'date' => '2026-05-04', 'account_id' => 'moti',
    'title' => '',
    'body' => "歯ブラシの近くに\n見えるところに\n「歯磨きはできるだけ丁寧にすること\n食べ終わってから磨くまでの時間の短さを\n意識しなさい」\nみたいなことを書いておくことで\nすごく歯磨きを大事にしてしまうみたいな\n認知テクニックだらけの家に住みたいと\n思います！",
    'intent' => null,
    'categories' => ['認知・行動'],
    'url' => null,
    'reactions' => ['💡' => 3, '✨' => 2, '🔥' => 1],
    'labels' => [['type'=>'misskey','url'=>'https://misskey.io/notes/abc123','memo'=>'2026-05-04 motiアカウントで投稿','added_at'=>'2026-05-04T12:00:00']],
    'eval' => [
      'comment' => '環境設計で行動を誘導するという視点が鮮明です。「書いておくだけで丁寧になる」という仕掛けへの気づきは、自己の認知バグを逆用する設計思想として完成度が高い。',
      'axes' => [['key'=>'actionability','label'=>'行動性','score'=>88],['key'=>'creativity','label'=>'創造性','score'=>76],['key'=>'specificity','label'=>'具体性','score'=>72],['key'=>'introspection','label'=>'内省度','score'=>65],['key'=>'humor','label'=>'ユーモア','score'=>58]],
    ],
  ],
  [
    'date' => '2026-05-03', 'account_id' => 'moti',
    'title' => '',
    'body' => "現在の責任は、結果について発生している",
    'intent' => null,
    'categories' => ['思想'],
    'url' => null,
    'reactions' => ['🤔' => 5, '💡' => 2],
    'labels' => [],
    'eval' => [
      'comment' => '一文に圧縮された論理は切れ味があります。「過去」や「行為」ではなく「結果」に責任を紐づける因果論的倫理観は、行動設計の出発点として機能しうる射程を持っています。',
      'axes' => [['key'=>'depth','label'=>'思考深度','score'=>91],['key'=>'clarity','label'=>'明瞭性','score'=>84],['key'=>'originality','label'=>'独自性','score'=>78],['key'=>'criticality','label'=>'批評性','score'=>70],['key'=>'introspection','label'=>'内省度','score'=>65]],
    ],
  ],
  [
    'date' => '2026-05-02', 'account_id' => 'moti',
    'title' => 'フィトケミカル',
    'body' => "フィトケミカル、とったらいいけど取らなくてもいい物質、取り入れていきたい",
    'intent' => null,
    'categories' => ['健康'],
    'url' => 'https://ja.wikipedia.org/wiki/%E3%83%95%E3%82%A3%E3%83%88%E3%82%B1%E3%83%9F%E3%82%AB%E3%83%AB',
    'reactions' => ['👀' => 4, '✨' => 1],
    'labels' => [],
    'eval' => [
      'comment' => '「取らなくてもいいがとりたい」という動機の正直さが好印象です。過剰でも義務でもない、ゆるい健康行動の優先度設計として機能しています。',
      'axes' => [['key'=>'specificity','label'=>'具体性','score'=>80],['key'=>'actionability','label'=>'行動性','score'=>75],['key'=>'expertise','label'=>'専門性','score'=>72],['key'=>'curiosity','label'=>'好奇心','score'=>68],['key'=>'timeliness','label'=>'時事性','score'=>55]],
    ],
  ],
  [
    'date' => '2026-05-01', 'account_id' => 'moti',
    'title' => '',
    'body' => "さのみっちさん曰く\n> そのときの自分ができることを最大限やれているならそれは「努力している」とされています",
    'intent' => null,
    'categories' => ['引用'],
    'url' => null,
    'reactions' => ['✨' => 3, '🔥' => 2, '🤔' => 1],
    'labels' => [],
    'eval' => [
      'comment' => '引用が単なる転載でなく、内省の入口として機能しています。外部の定義を取り込みながら自己評価の基準を再構築しようとする姿勢が、次の投稿（努力の定義）への布石になっています。',
      'axes' => [['key'=>'introspection','label'=>'内省度','score'=>85],['key'=>'vulnerability','label'=>'素直さ','score'=>82],['key'=>'depth','label'=>'思考深度','score'=>78],['key'=>'clarity','label'=>'明瞭性','score'=>74],['key'=>'empathy','label'=>'共感性','score'=>71]],
    ],
  ],
  [
    'date' => '2026-04-30', 'account_id' => 'moti',
    'title' => '',
    'body' => "周りの人たちは短い文章を羅列して投稿するのに\nもちさんは長文ばっかり投稿するのは\n何故か？みたいなものを研究して\n思考形態の違いを探ってみませんか？",
    'intent' => "misskeyでかなり短文にコミュニケーションをとる人は見かけるけども、もちさんみたいに長文ばっかり垂れ流して思考の整理をしている人を見かけなくてなんとなく思ったこと、そんなにやろうとも思っていない",
    'categories' => ['思想', '認知・行動'],
    'url' => null,
    'reactions' => ['🤔' => 4, '💭' => 3],
    'labels' => [],
    'eval' => [
      'comment' => '自分の思考スタイルを他者との差異から浮き彫りにする観察眼が鋭い。「やろうとも思っていない」という留保が、観察者としての適切な距離を保っており、過剰な自己分析に陥っていません。',
      'axes' => [['key'=>'introspection','label'=>'内省度','score'=>83],['key'=>'curiosity','label'=>'好奇心','score'=>79],['key'=>'depth','label'=>'思考深度','score'=>76],['key'=>'sociality','label'=>'社会接続性','score'=>72],['key'=>'originality','label'=>'独自性','score'=>68]],
    ],
  ],
  [
    'date' => '2026-04-29', 'account_id' => 'moti',
    'title' => '',
    'body' => "もちさんの「努力」の定義には暗黙的に持続的な苦しみが含まれていますね！\n- 一瞬の最大火力 → 苦しみがない → 努力でない\n- やりたいことを長時間やる → 苦しみがない → 努力でない\n- やりたくないことを継続する → 苦しみがある → 努力\n苦しみの持続時間と強度が努力の判定基準になっている気がします！\nただそのせいで、本来寝るべきなのに粘って作業するのを努力のような気がしてしまい、無理に続けてしまう悪癖があります！",
    'intent' => "もちさんが頑張っている、努力しているという評価を受けた時に、認知的にはしていないつもりなので努力要件を調べてみた時の話です",
    'categories' => ['思想', '認知・行動'],
    'url' => null,
    'reactions' => ['💡' => 6, '🤯' => 3, '✨' => 2],
    'labels' => [['type'=>'verified','url'=>'','memo'=>'自己分析として検証済み、認知行動療法文脈でも有効','added_at'=>'2026-04-29T12:00:00']],
    'eval' => [
      'comment' => '自己概念の解体と再構築が、箇条書きによって驚くほど明快に整理されています。「苦しみ＝努力」フレームが睡眠削りを正当化しているという気づきは、認知行動療法的な自己介入として即座に使えるレベルの洞察です。',
      'axes' => [['key'=>'introspection','label'=>'内省度','score'=>92],['key'=>'depth','label'=>'思考深度','score'=>88],['key'=>'vulnerability','label'=>'素直さ','score'=>85],['key'=>'clarity','label'=>'明瞭性','score'=>79],['key'=>'actionability','label'=>'行動性','score'=>71]],
    ],
  ],
  [
    'date' => '2026-04-28', 'account_id' => 'moti',
    'title' => '',
    'body' => "言語学的に最強の雑談トピックは天気の話らしい",
    'intent' => "ゆる言語学ラジオ、水野さんの主張",
    'categories' => ['引用', '言語'],
    'url' => null,
    'reactions' => ['👀' => 5, '💡' => 2],
    'labels' => [],
    'eval' => [
      'comment' => '短文ながら、社会言語学への入口として十分な引力があります。天気が「ファティック・コミュニケーション」として機能しているという視点は、日常会話の設計に応用できます。',
      'axes' => [['key'=>'sociality','label'=>'社会接続性','score'=>81],['key'=>'expertise','label'=>'専門性','score'=>74],['key'=>'specificity','label'=>'具体性','score'=>70],['key'=>'humor','label'=>'ユーモア','score'=>66],['key'=>'timeliness','label'=>'時事性','score'=>62]],
    ],
  ],
  [
    'date' => '2026-04-27', 'account_id' => 'moti',
    'title' => '',
    'body' => "ワクワクをお茶に求めてないものの\nワクワクするものであると言う話で飲んでいるので\n上がった期待が体験を悪くしています！",
    'intent' => "ONICHAを飲んだ時の感想、公民館のでかいポッドに、麦茶のパックを一つだけ入れた時の味がした",
    'categories' => ['体験', '健康'],
    'url' => null,
    'reactions' => ['😂' => 4, '✨' => 2],
    'labels' => [],
    'eval' => [
      'comment' => '「公民館のでかいポッドに麦茶パック一つ」という具体的な比喩が、期待値管理という抽象的な教訓を鮮明にしています。予期せぬ体験を即座に認知的フレームに落とし込む速度が印象的です。',
      'axes' => [['key'=>'specificity','label'=>'具体性','score'=>86],['key'=>'emotion','label'=>'感情強度','score'=>83],['key'=>'humor','label'=>'ユーモア','score'=>77],['key'=>'vulnerability','label'=>'素直さ','score'=>72],['key'=>'creativity','label'=>'創造性','score'=>68]],
    ],
  ],
  [
    'date' => '2026-04-26', 'account_id' => 'moti',
    'title' => '',
    'body' => "もちさんの人間関係の設計は\n深さより質、数より相性\nなので、こうなっています！\n(* 'ᵕ' )ｲｲﾖｯ!(* 'ᵕ' )ｲｲﾖｯ!(* 'ᵕ' )ｲｲﾖｯ!\nいい人だとしても\nキャリブレーションコストが高そうだとお断りします！",
    'intent' => "好意的でも、害があると判断すれば距離をとります！常に敬語なのは距離感が近すぎないようになるという利点もあります！",
    'categories' => ['思想', '人間関係'],
    'url' => null,
    'reactions' => ['✨' => 5, '💡' => 3, '🎯' => 2],
    'labels' => [],
    'eval' => [
      'comment' => '人間関係にコスト概念を持ち込む設計思想が明快です。「好意的でも有害なら距離を置く」という判断基準の明文化は、感情ではなく原則に基づく関係設計の証明。敬語を距離調整ツールとして意図的に使用している点も、言語と人間関係設計の交点として興味深い。',
      'axes' => [['key'=>'originality','label'=>'独自性','score'=>87],['key'=>'clarity','label'=>'明瞭性','score'=>83],['key'=>'introspection','label'=>'内省度','score'=>79],['key'=>'actionability','label'=>'行動性','score'=>76],['key'=>'criticality','label'=>'批評性','score'=>71]],
    ],
  ],
  [
    'date' => '2026-04-25', 'account_id' => 'moti',
    'title' => '',
    'body' => "服とタオルは、乾きやすく、毛玉ができないが最優先、次に値段\nその基準で選ぶことで余計なリソースをとらずに済みます。",
    'intent' => "こだわりがないわけではなく、機能的で情報量が少ないのがいいというこだわりがあるということですよ、デザインはシンプルなのがいいですね！",
    'categories' => ['認知・行動'],
    'url' => null,
    'reactions' => ['💡' => 4, '👀' => 2],
    'labels' => [],
    'eval' => [
      'comment' => '消費行動の最適化軸を「機能」と「情報量の少なさ」に絞り込む思想が一貫しています。これは節約ではなく、認知的コストの意図的削減戦略です。「こだわりがないのではなく、シンプルへのこだわりがある」という逆転表現が、思想の解像度を示しています。',
      'axes' => [['key'=>'actionability','label'=>'行動性','score'=>84],['key'=>'clarity','label'=>'明瞭性','score'=>81],['key'=>'specificity','label'=>'具体性','score'=>78],['key'=>'originality','label'=>'独自性','score'=>72],['key'=>'introspection','label'=>'内省度','score'=>68]],
    ],
  ],
  [
    'date' => '2026-04-24', 'account_id' => 'moti',
    'title' => '',
    'body' => "もちさんはとてもやわらかい",
    'intent' => null,
    'categories' => ['もちさん設定'],
    'url' => null,
    'reactions' => ['✨' => 7, '❤️' => 4],
    'labels' => [],
    'eval' => [
      'comment' => '最短で最大の情報密度を持つ投稿です。「やわらかい」という形容が、物理的・精神的・対人的の三層で同時に成立しています。これほど多義的な自己表現は珍しい。',
      'axes' => [['key'=>'vulnerability','label'=>'素直さ','score'=>95],['key'=>'emotion','label'=>'感情強度','score'=>82],['key'=>'humor','label'=>'ユーモア','score'=>78],['key'=>'originality','label'=>'独自性','score'=>74],['key'=>'creativity','label'=>'創造性','score'=>70]],
    ],
  ],
  [
    'date' => '2026-04-23', 'account_id' => 'moti',
    'title' => '',
    'body' => "もちさんは\n元気の無い人を見掛けると\nもちパワーって名前の\n得体の知れ無い何かを配っているのですが\n調査の結果、もちパワーはメンタルが強くなり、幸せな気分になれるホルモンの1種だそうです！\n依存性があるそうです！\n覚えておいてください！\n(* 'ᵕ' )ｲｲﾖｯ!(* 'ᵕ' )ｲｲﾖｯ!",
    'intent' => "もちパワーはg単位でもちさんがためている物質である",
    'categories' => ['もちさん設定'],
    'url' => null,
    'reactions' => ['😂' => 6, '❤️' => 5, '✨' => 3],
    'labels' => [],
    'eval' => [
      'comment' => 'もちパワーを科学的物質として定義するユーモアと、「依存性」「ホルモン」という擬似学術的フレームの組み合わせが巧みです。自己神話化の一種として機能しており、もちさんのキャラクター定義として説得力があります。',
      'axes' => [['key'=>'humor','label'=>'ユーモア','score'=>93],['key'=>'creativity','label'=>'創造性','score'=>88],['key'=>'emotion','label'=>'感情強度','score'=>82],['key'=>'originality','label'=>'独自性','score'=>79],['key'=>'sociality','label'=>'社会接続性','score'=>75]],
    ],
  ],
  [
    'date' => '2026-04-22', 'account_id' => 'moti',
    'title' => '',
    'body' => "なのでもちが上手くいった分は\nそのうちもちパワーとして\n皆さんに還元されていくかもしれません！\nうおおおおおおおおおおおお！\n𝑩𝑰𝑮𝑳𝑶𝑽𝑬──────────",
    'intent' => null,
    'categories' => ['もちさん設定'],
    'url' => null,
    'reactions' => ['🔥' => 8, '❤️' => 6, '✨' => 4],
    'labels' => [],
    'eval' => [
      'comment' => '前投稿の世界観を経済的フレーム（還元）で拡張しており、もちパワー神話の連続性が際立ちます。「うおおお」という感情爆発と𝑩𝑰𝑮𝑳𝑶𝑽𝑬の優雅なフォントの対比が絶妙です。感情の振り幅をそのまま文体に反映させる技法が独特。',
      'axes' => [['key'=>'emotion','label'=>'感情強度','score'=>95],['key'=>'humor','label'=>'ユーモア','score'=>88],['key'=>'creativity','label'=>'創造性','score'=>84],['key'=>'sociality','label'=>'社会接続性','score'=>80],['key'=>'vulnerability','label'=>'素直さ','score'=>76]],
    ],
  ],
  [
    'date' => '2026-04-21', 'account_id' => 'moti',
    'title' => '',
    'body' => "都度準備じゃなくて、毎日準備しておけばいい\n事が起こる前に対策しておいて最悪のパターンは知っていれば知っているだけいい",
    'intent' => null,
    'categories' => ['認知・行動', '思想'],
    'url' => null,
    'reactions' => ['💡' => 5, '🎯' => 3],
    'labels' => [],
    'eval' => [
      'comment' => '「都度準備」から「常時準備」への思考転換は、認知的負荷を事前に分散させる設計思想です。最悪パターンを事前に知ることでレジリエンスを高めるという発想は、ストア哲学の「否定的想像」とも接続しています。',
      'axes' => [['key'=>'actionability','label'=>'行動性','score'=>89],['key'=>'clarity','label'=>'明瞭性','score'=>85],['key'=>'depth','label'=>'思考深度','score'=>79],['key'=>'specificity','label'=>'具体性','score'=>74],['key'=>'originality','label'=>'独自性','score'=>68]],
    ],
  ],
];

$created = 0;
foreach ($posts as $seed) {
    $id  = date('Ymd', strtotime($seed['date'])) . '_' . bin2hex(random_bytes(3));
    $now = $seed['date'] . 'T12:00:00';

    $post = [
        'id'             => $id,
        'account_id'     => $seed['account_id'],
        'title'          => $seed['title'],
        'body'           => $seed['body'],
        'intent'         => $seed['intent'],
        'url'            => $seed['url'],
        'categories'     => $seed['categories'],
        'labels'         => $seed['labels'],
        'reactions'      => $seed['reactions'],
        'comments'       => [],
        'repost'         => false,
        'repost_from'    => null,
        'archive_at'     => null,
        'categorized_at' => $now,
        'created_at'     => $now,
        'updated_at'     => $now,
    ];
    json_write(DATA_POSTS . "/{$id}.json", $post);

    $eval = [
        'post_id'    => $id,
        'evaluation' => [
            'comment'      => $seed['eval']['comment'],
            'axes'         => $seed['eval']['axes'],
            'generated_at' => $now,
        ],
        'replies' => [],
    ];
    json_write(DATA_EVALS . "/{$id}.json", $eval);

    echo "✓ {$id}  ({$seed['date']})\n";
    $created++;
}

echo "\n{$created} 件の投稿と評価データを作成しました\n";
