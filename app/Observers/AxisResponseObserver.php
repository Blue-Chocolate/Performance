<?php


namespace App\Observers;


use App\Models\AxisResponse;


class AxisResponseObserver
{
public function saved(AxisResponse $ar)
{
$this->recalcOrganization($ar->organization);
}


public function deleted(AxisResponse $ar)
{
$this->recalcOrganization($ar->organization);
}


protected function recalcOrganization($org)
{
$scored = $org->axisResponses()->whereNotNull('admin_score')->get();


if ($scored->isEmpty()) {
$org->update(['final_score' => null]);
return;
}


// use axis weights
$sumWeighted = 0;
foreach ($scored as $r) {
$weight = $r->axis->weight ?? 25;
$sumWeighted += ($r->admin_score * ($weight / 100));
}


$org->update(['final_score' => round($sumWeighted, 2)]);
}
}