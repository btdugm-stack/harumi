<?php

function harumi_item(string $name, string $category, int $regular, ?int $large = null, bool $favorite = false): array
{
    return [
        'id' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')),
        'name' => $name,
        'category' => $category,
        'regular' => $regular,
        'large' => $large,
        'favorite' => $favorite,
    ];
}

return [
    harumi_item('Usucha', 'Premium Matcha', 12000, 15000),
    harumi_item('Signature Matcha Latte', 'Premium Matcha', 18000, 24000, true),
    harumi_item('Sea Salt Matcha Latte', 'Premium Matcha', 18000, 24000, true),
    harumi_item('Salted Caramel Matcha Latte', 'Premium Matcha', 18000, 24000),
    harumi_item('Oreo Matcha Latte', 'Premium Matcha', 18000, 24000, true),
    harumi_item('Brown Sugar Matcha Latte', 'Premium Matcha', 18000, 24000),
    harumi_item('Coconut Cloud Matcha Latte', 'Premium Matcha', 18000, 24000),
    harumi_item('Coconut Pure Matcha Latte', 'Premium Matcha', 18000, 24000),
    harumi_item('Honey Matcha Latte', 'Premium Matcha', 18000, 24000),
    harumi_item('Osmanthus Matcha Latte', 'Premium Matcha', 20000, 26000),
    harumi_item('Vanilla Matcha Latte', 'Premium Matcha', 20000, 26000),
    harumi_item('Strawberry Matcha Latte', 'Premium Matcha', 22000, 28000),
    harumi_item('Mango Matcha Latte', 'Premium Matcha', 22000, 28000, true),
    harumi_item('Coffee Matcha Latte', 'Premium Matcha', 23000, 30000),
    harumi_item('Lotus Biscoff Matcha Latte', 'Premium Matcha', 25000, 32000, true),

    harumi_item('Americano', 'Coffee', 10000, 15000),
    harumi_item('Cafe Latte', 'Coffee', 10000, 15000),
    harumi_item('Spanish Latte', 'Coffee', 12000, 17000),
    harumi_item('Aren Latte', 'Coffee', 13000, 18000, true),
    harumi_item('Caramel Latte', 'Coffee', 15000, 18000, true),
    harumi_item('Hazelnut Latte', 'Coffee', 15000, 20000),
    harumi_item('Pandan Latte', 'Coffee', 15000, 20000),
    harumi_item('Vanilla Latte', 'Coffee', 15000, 20000, true),
    harumi_item('Oreo Coffee Latte', 'Coffee', 16000, 21000),
    harumi_item('Caramel Macchiato', 'Coffee', 16000, 21000, true),
    harumi_item('Butterscotch Sea Salt Latte', 'Coffee', 17000, 22000),
    harumi_item('Coconut Aren Latte', 'Coffee', 18000, 23000),

    harumi_item('Thai Tea', 'Non Coffee', 10000, 12000, true),
    harumi_item('Red Velvet', 'Non Coffee', 12000, 16000),
    harumi_item('Taro Latte', 'Non Coffee', 12000, 16000),
    harumi_item('Thai Tea Caramel Cloud', 'Non Coffee', 13000, 15000),
    harumi_item('Signature Choco', 'Non Coffee', 13000, 15000, true),
    harumi_item('Coconut Mango', 'Non Coffee', 14000, 18000),
    harumi_item('Coconut Lychee', 'Non Coffee', 14000, 18000),
    harumi_item('Harumilk Mango', 'Non Coffee', 15000, 18000, true),
    harumi_item('Harumilk Kiwi', 'Non Coffee', 15000, 18000),
    harumi_item('Harumilk Blueberry', 'Non Coffee', 15000, 18000),
    harumi_item('Harumilk Strawberry', 'Non Coffee', 15000, 18000),
    harumi_item('Strawberry Choco', 'Non Coffee', 15000, 18000),

    harumi_item('Indomie Goreng', 'Food', 10000),
    harumi_item('Indomie Kuah', 'Food', 10000),
    harumi_item('Ricebowl Ayam Sambal Matah', 'Food', 24000),
    harumi_item('Ricebowl Ayam Sambal Rujak', 'Food', 24000),
    harumi_item('Ricebowl Chicken Teriyaki', 'Food', 24000),
    harumi_item('Ricebowl Beef Yakiniku', 'Food', 28000),
    harumi_item('Extra Telur', 'Food', 5000),

    harumi_item('French Fries', 'Snacks', 10000, 15000),
    harumi_item('Dimsum Original', 'Snacks', 15000, 20000),
    harumi_item('Dimsum Kuah Creamy Chili Oil', 'Snacks', 15000, 20000),
    harumi_item('Cireng Kuah Creamy Chili Oil', 'Snacks', 15000, 20000),
    harumi_item('Tahu Aci Kuah Creamy Chili Oil', 'Snacks', 15000),
    harumi_item('Pempek Tenggiri', 'Snacks', 20000),

    harumi_item('Burnt Cheesecake', 'Dessert', 28000),
    harumi_item('Cheesecake Blueberry', 'Dessert', 30000),
    harumi_item('Cheesecake Strawberry', 'Dessert', 30000),
    harumi_item('Cheesecake Matcha', 'Dessert', 32000),
    harumi_item('Cheesecake Lotus', 'Dessert', 32000),
];
