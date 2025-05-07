<?php

namespace Database\Seeders;

use App\Models\DeploymentRing;
use Illuminate\Database\Seeder;

class DeploymentRingSeeder extends Seeder
{
    public function run()
    {
        $rings = [
            [
                'name' => 'Insiders',
                'description' => 'Early access to new features and updates',
                'order' => 1,
                'version' => config('app.version'),
                'auto_update' => true
            ],
            [
                'name' => 'Early Adopters',
                'description' => 'Receive updates after successful deployment to Insiders',
                'order' => 2,
                'version' => config('app.version'),
                'auto_update' => true
            ],
            [
                'name' => 'Production',
                'description' => 'Standard production environment',
                'order' => 3,
                'version' => config('app.version'),
                'auto_update' => false
            ]
        ];
        
        foreach ($rings as $ring) {
            DeploymentRing::create($ring);
        }
    }
}