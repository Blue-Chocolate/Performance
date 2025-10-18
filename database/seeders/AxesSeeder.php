<?php


namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Axis;


class AxesSeeder extends Seeder
{
public function run()
{
$axes = [
['title' => 'Strategy', 'description' => 'Strategy description', 'weight' => 25],
['title' => 'Impact', 'description' => 'Impact description', 'weight' => 25],
['title' => 'Financial', 'description' => 'Financial description', 'weight' => 25],
['title' => 'Operations', 'description' => 'Operations description', 'weight' => 25],
];


foreach ($axes as $a) Axis::updateOrCreate(['title' => $a['title']], $a);
}
}