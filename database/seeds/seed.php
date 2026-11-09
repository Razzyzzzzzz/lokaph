<?php
// Run once after importing schema.sql:  php database/seeds/seed.php
require __DIR__ . '/../../app/Core/Core.php';
if (q('SELECT COUNT(*) FROM users')->fetchColumn()) exit("Already seeded.\n");
foreach (['Bakery & Cafe', 'Grocery & Sari-sari', 'Fashion & Apparel', 'Electronics', 'Beauty & Salon', 'Home & Furniture', 'Pet Shop', 'Handmade & Crafts', 'Services'] as $n) q('INSERT INTO business_categories(name) VALUES(?)', [$n]);
foreach (['Food and Drinks', 'Clothing and Fashion', 'Electronics', 'Beauty and Personal Care', 'Home and Furniture', 'Pet Supplies', 'Handmade Products', 'Grocery and Essentials', 'Services'] as $n) q('INSERT INTO product_categories(name) VALUES(?)', [$n]);
$mk = function ($r, $n, $e, $p) { q('INSERT INTO users(role_id,name,email,password) VALUES(?,?,?,?)', [$r, $n, $e, password_hash($p, PASSWORD_DEFAULT)]); return db()->lastInsertId(); };
$mk(3, 'Admin', 'admin@lokalph.test', 'Admin123!'); $owner = $mk(2, 'Sample Owner', 'owner@lokalph.test', 'Owner123!'); $mk(1, 'Sample Customer', 'customer@lokalph.test', 'Customer123!');
// name, biz cat, description, address, city, province, lat, lng, phone, [[product, product cat, price, stock, description]]
$biz = [
 ['Local Bakery', 1, 'Fresh pandesal and cakes baked every morning.', 'Mabini St., Ermita', 'Manila', 'Metro Manila', 14.5833, 120.9850, '0917 100 0001', [['Pandesal (10 pcs)', 1, 45, 100, 'Hot from the oven.'], ['Ube Cake', 1, 480, 8, 'Whole ube chiffon cake.']]],
 ['Josh Mini Grocery', 2, 'Neighborhood sari-sari and grocery essentials.', 'Lacson Ave., Sampaloc', 'Manila', 'Metro Manila', 14.6100, 120.9890, '0917 100 0002', [['Rice 5kg', 8, 285, 40, 'Premium white rice.'], ['Cooking Oil 1L', 8, 95, 60, 'Coconut cooking oil.']]],
 ["Maria's Coffee Shop", 1, 'Local beans, brewed strong.', 'M.H. del Pilar St., Malate', 'Manila', 'Metro Manila', 14.5700, 120.9930, '0917 100 0003', [['Iced Barako', 1, 120, 50, 'Batangas barako over ice.'], ['Ensaymada', 1, 65, 30, 'Buttery and cheesy.']]],
 ['Kuya Rey Phone Repair', 4, 'Screen replacement and phone accessories.', 'Hidalgo St., Quiapo', 'Manila', 'Metro Manila', 14.5990, 120.9830, '0917 100 0004', [['Phone Charger 20W', 3, 350, 25, 'Fast charger, 6-month warranty.'], ['Screen Repair Service', 9, 1500, 99, 'Most phones, same day.']]],
 ['Pet Haven', 7, 'Everything for dogs and cats.', 'Tomas Morato Ave.', 'Quezon City', 'Metro Manila', 14.6350, 121.0370, '0917 100 0005', [['Dog Food 1kg', 6, 220, 35, 'Adult dog dry food.'], ['Cat Litter 5L', 6, 180, 20, 'Clumping litter.']]],
 ['Cebu Handmade Crafts', 8, 'Woven bags and shell crafts from local artisans.', 'Colon St.', 'Cebu City', 'Cebu', 10.3100, 123.8900, '0917 100 0006', [['Abaca Tote Bag', 7, 650, 12, 'Hand-woven abaca.'], ['Shell Wind Chime', 7, 320, 15, 'Handmade shell chime.']]],
];
foreach ($biz as [$n, $c, $d, $a, $ci, $pr, $la, $ln, $ph, $items]) {
  q('INSERT INTO businesses(owner_id,category_id,name,description,phone,status,verified,featured,is_sample) VALUES(?,?,?,?,?,"approved",1,?,1)', [$owner, $c, $n, $d, $ph, $c < 3 ? 1 : 0]); $id = db()->lastInsertId();
  q('INSERT INTO business_locations(business_id,address,city,province,lat,lng) VALUES(?,?,?,?,?,?)', [$id, $a, $ci, $pr, $la, $ln]);
  for ($i = 0; $i < 7; $i++) q('INSERT INTO business_hours(business_id,day,opens,closes,is_closed) VALUES(?,?,?,?,0)', [$id, $i, $i < 6 ? '08:00' : '09:00', $i < 6 ? '20:00' : '17:00']);
  foreach ($items as [$pn, $pc, $pp, $ps, $pd]) q('INSERT INTO products(business_id,category_id,name,description,price,stock) VALUES(?,?,?,?,?,?)', [$id, $pc, $pn, $pd, $pp, $ps]);
}
echo "Seeded. Logins: admin@lokalph.test / Admin123!, owner@lokalph.test / Owner123!, customer@lokalph.test / Customer123!\n";
