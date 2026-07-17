<?php
/**
 * Database Seeder - Run once to populate demo data
 * Access: https://yourdomain.com/seed.php
 * Delete after use!
 */

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/database.php';

echo "<pre>";
echo "🌱 Nepal News Portal - Database Seeder\n";
echo "======================================\n\n";

try {
    // Ensure tables exist
    echo "📋 Creating tables...\n";
    require __DIR__ . '/src/init.php';
    echo "✓ Tables ready\n\n";
    
    $db = get_db();
    $driver = db_driver();
    
    // Categories
    echo "📂 Inserting categories...\n";
    $categories = [
        ['Economy', 'arthatantra', 'अर्थतन्त्र', '#059669', 'trending-up', 'Economic news and analysis'],
        ['Banking', 'banking', 'बैंकिङ', '#2563EB', 'landmark', 'Banking and finance sector news'],
        ['Share Market', 'share-bazar', 'शेयर बजार', '#7C3AED', 'line-chart', 'Stock market and investment news'],
        ['Politics', 'rajniti', 'राजनीति', '#DC2626', 'flag', 'Political news and updates'],
        ['Technology', 'prabidhi', 'प्रविधि', '#0891B2', 'cpu', 'Technology and innovation news'],
        ['Sports', 'khel-kurod', 'खेलकुद', '#EA580C', 'trophy', 'Sports news and updates'],
        ['Society', 'samaj', 'समाज', '#4F46E5', 'users', 'Social and community news'],
        ['Tourism', 'par-tourism', 'पर्यटन', '#0D9488', 'map', 'Tourism and travel news'],
        ['World', 'sansar', 'संसार', '#6366F1', 'globe', 'International news'],
        ['Opinion', 'opinions', 'विचार', '#8B5CF6', 'message-square', 'Opinion and editorial pieces'],
        ['Insurance', 'bima', 'बिमा', '#10B981', 'shield', 'Insurance sector news'],
        ['Corporate', 'corporate', 'कर्पोरेट', '#F59E0B', 'briefcase', 'Corporate and business news'],
    ];
    
    $cat_ids = [];
    foreach ($categories as $cat) {
        $slug = $cat[1];
        $check = db_fetch("SELECT id FROM categories WHERE slug = ?", [$slug]);
        if (!$check) {
            if ($driver === 'mysql') {
                $db->exec("INSERT INTO categories (name, slug, name_np, color, icon, description) VALUES (?,?,?,?,?,?)", $cat);
            } else {
                $db->exec("INSERT INTO categories (name, slug, name_np, color, icon, description) VALUES (?,?,?,?,?,?)", $cat);
            }
        }
        $cat_ids[$slug] = db_fetch("SELECT id FROM categories WHERE slug = ?", [$slug])['id'] ?? 0;
        echo "  ✓ {$cat[2]}\n";
    }
    
    // Authors
    echo "\n👤 Inserting authors...\n";
    $authors = [
        ['कमल श्रेष्ठ', 'kamal-shreshta', 'kamal@newsportal.com', 'कमल श्रेष्ठ, अर्थतन्त्र विश्लेषक हुनुहुन्छ।', 'Senior Editor'],
        ['रवि पन्त', 'ravi-pant', 'ravi@newsportal.com', 'रवि पन्त, प्रविधि विशेषज्ञ।', 'Tech Editor'],
        ['सीता राई', 'sita-rai', 'sita@newsportal.com', 'सीता राई, खेलकुद प्रतिवेदक।', 'Sports Reporter'],
    ];
    
    $author_ids = [];
    foreach ($authors as $author) {
        $slug = $author[1];
        $check = db_fetch("SELECT id FROM authors WHERE slug = ?", [$slug]);
        if (!$check) {
            $db->exec("INSERT INTO authors (name, slug, email, bio, role) VALUES (?,?,?,?,?)", $author);
        }
        $author_ids[$slug] = db_fetch("SELECT id FROM authors WHERE slug = ?", [$slug])['id'] ?? 0;
        echo "  ✓ {$author[0]}\n";
    }
    
    // Sample Articles
    echo "\n📰 Inserting sample articles...\n";
    $articles = [
        [
            'नेपालको अर्थतन्त्रमा सुधारको संकेत',
            'nepal-economy-improvement',
            'नेपाली अर्थतन्त्रमा हालका दिनमा सुधार देखिन थालेको छ।',
            '<p>नेपालको अर्थतन्त्रमा हालका दिनमा सुधार देखिन थालेको छ। व्यापार घाटा कम भएको छ भने रेमिट्यान्स प्रवाह बढेको छ।</p><p>नेपाल राष्ट्र बैंकका अनुसार चालु खाता सकारात्मक भएको छ। विदेशी मुद्रा सञ्चिति पनि उल्लेख्य बढेको छ।</p><p>केही महिना अगाडि मात्र अर्थतन्त्र संकटमा रहेको विश्लेषकहरूले बताएका थिए। तर अहिले परिस्थिति बदलिएको छ।</p>',
            'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=800',
            'arthatantra', 'kamal-shreshta', 1, 0, 'published', 1250
        ],
        [
            'शेयर बजारमा उत्साहजनक वृद्धि',
            'share-market-upsurge',
            'नेपाली शेयर बजारमा उत्साहजनक वृद्धि भएको छ।',
            '<p>नेपालको शेयर बजारमा उत्साहजनक वृद्धि भएको छ। नेप्से इन्डेक्स ५० अंकले बढेर २,५०० को स्तरमा पुगेको छ।</p><p>लगानीकर्ताहरूमा उत्साह देखिन्छ। धेरै कम्पनीहरूको शेयर मूल्य बढेको छ।</p>',
            'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800',
            'share-bazar', 'kamal-shreshta', 1, 0, 'published', 890
        ],
        [
            'नेपाली प्रविधि क्षेत्रमा नयाँ आयाम',
            'nepal-tech-new-dimension',
            'नेपाली प्रविधि क्षेत्रमा नयाँ आयाम थपिएको छ।',
            '<p>नेपाली प्रविधि क्षेत्रमा नयाँ आयाम थपिएको छ। स्टार्टअप संस्कृतिको विकास भएको छ।</p><p>युवाहरूमा उद्यमशीलताको भावना बढेको छ। धेरै नयाँ प्रविधि कम्पनीहरू स्थापना भएका छन्।</p>',
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800',
            'prabidhi', 'ravi-pant', 0, 1, 'published', 1560
        ],
        [
            'क्रिकेट विश्वकप: नेपालको यात्रा',
            'cricket-worldcup-nepal',
            'क्रिकेट विश्वकपमा नेपालको यात्रा रोचक बनेको छ।',
            '<p>क्रिकेट विश्वकपमा नेपालको यात्रा रोचक बनेको छ। नेपालले उत्कृष्ट प्रदर्शन गरेको छ।</p><p>रुबेन क्रेगको कप्तानीमा नेपाली टोलीले उत्कृष्ट खेल देखाएको छ।</p>',
            'https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800',
            'khel-kurod', 'sita-rai', 0, 0, 'published', 2100
        ],
        [
            'पर्यटन क्षेत्र: कोभिड पछिको पुनरागमन',
            'tourism-post-covid',
            'पर्यटन क्षेत्र कोभिड पछि पुनरागमन गर्दै।',
            '<p>नेपालको पर्यटन क्षेत्र कोभिड पछि पुनरागमन गर्दै छ। पर्यटकहरूको आगमन बढेको छ।</p><p>माउन्ट एवरेस्ट र अन्य पर्यटकीय स्थलहरूमा पर्यटकहरूको चाप बढेको छ।</p>',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
            'par-tourism', 'sita-rai', 0, 0, 'published', 980
        ],
    ];
    
    foreach ($articles as $article) {
        $title = $article[0];
        $slug = $article[1];
        $check = db_fetch("SELECT id FROM articles WHERE slug = ?", [$slug]);
        if (!$check) {
            $published_at = date('Y-m-d H:i:s', rand(time() - 86400*30, time()));
            $db->exec(
                "INSERT INTO articles (title, slug, summary, content, image_url, category_id, author_id, featured, is_breaking, status, views, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                array_merge($article, [$published_at])
            );
            echo "  ✓ {$title}\n";
        }
    }
    
    // Settings
    echo "\n⚙️ Updating settings...\n";
    $settings = [
        'site_name' => 'न्यूज पोर्टल नेपाल',
        'site_name_en' => 'Nepal News Portal',
        'tagline' => 'नेपालको विश्वसनीय समाचार पोर्टल',
        'tagline_en' => "Nepal's Trusted News Portal",
    ];
    
    foreach ($settings as $key => $value) {
        $db->exec("INSERT OR REPLACE INTO settings (`key`, value) VALUES (?, ?)", [$key, $value]);
        echo "  ✓ {$key}\n";
    }
    
    echo "\n✅ Database seeded successfully!\n";
    echo "================================\n";
    echo "🌐 Visit: https://{$_SERVER['HTTP_HOST']}/\n";
    echo "\n🔒 Delete seed.php after use!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
echo "</pre>";
