<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CarModel;
use App\Models\BranchRegion;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name'=>'admin',
                'login'=>'dell',
                'password'=>bcrypt('dell123'),
                'role'=>'admin'
            ]
            ];
        $car_models = [
         [
            'number'=>'01268YGA',
            'model'=>'LABO',
            'active'=>1,
            'is_del'=>0,
            'used'=>12
        ]
        ]; 

        $regions = [
            [
                'name'=>'Не указанные филиалы',
            ],
            [
                'name'=>'Ташкентская область',
            ],
            [
                'name'=>'Город Ташкент',
            ],
            [
                'name'=>'Кашкадарьинская область',
            ],
            [
                'name'=>'Навоийская область',
            ],
            [
                'name'=>'Андижанская область',
            ],
            [
                'name'=>'Ферганская область',
            ]
            ];



            foreach ($users as $key => $value){
                User::create($value);
            }

            foreach ($car_models as $key => $value){
                CarModel::create($value);
            }

            foreach ($regions as $key => $value){
                BranchRegion::create($value);
            }
    }
}
