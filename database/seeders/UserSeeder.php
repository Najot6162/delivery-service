<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CarModel;
use App\Models\BranchRegion;
use App\Models\ConfigTime;
use App\Models\Menus;
use App\Models\RoleList;
use App\Models\UserPermission;

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
                'email' => 'admin@gmail.com',
                'is_admin' => 1,
                'phone' => '998901234567',
                'address' => 'Yunusobod',
                'active' => 1,
                'branch_id' => 1,
                'name' => 'admin',
                'password' => bcrypt('123456'),
                'role_id' => 1
            ],
        ];
        $car_models = [
            [
                'number' => '01268YGA',
                'model' => 'LABO',
                'active' => 1,
                'is_del' => 0,
                'used' => 12
            ],
            [
                'number' => '01268YGA',
                'model' => 'LABO',
                'active' => 1,
                'is_del' => 0,
                'used' => 12
            ],
            [
                'number' => '01268YGA',
                'model' => 'LABO',
                'active' => 0,
                'is_del' => 0,
                'used' => 12
            ]
        ];

        $regions = [
            [
                'name' => 'Не указанные филиалы',
            ],
            [
                'name' => 'Ташкентская область',
            ],
            [
                'name' => 'Город Ташкент',
            ],
            [
                'name' => 'Кашкадарьинская область',
            ],
            [
                'name' => 'Навоийская область',
            ],
            [
                'name' => 'Андижанская область',
            ],
            [
                'name' => 'Ферганская область',
            ]
        ];

        $config_time = [
            [
                'user_id' => 1,
                'time' => 48,
                'active' => 1,
            ],
            [
                'user_id' => 2,
                'time' => 72,
                'active' => 0,
            ],
            [
                'user_id' => 1,
                'time' => 36,
                'active' => 0,
            ]
        ];

        $role_lists = [
            [
                'name' => 'Administrators',
            ],
            [
                'name' => 'Managers',
            ],
            [
                'name' => 'Warehouse Manager',
            ],
            [
                'name' => 'Driver',
            ],
            [
                'name' => 'Service Center',
            ],
            [
                'name' => 'Branch',
            ]
        ];

        $menus = [
            [
                'name' => 'Перемещения',
                'web_type'=>1.1,
                'name_path'=>'relocation',
                'type'=>1
            ],
            [
                'name' => 'Заявки',
                'web_type'=>1.2,
                'name_path'=>'pick-list',
                'type'=>1
            ],
//            [
//                'name' => 'Колл-центр',
//                'web_type'=>1.3,
//                'name_path'=>'call_center',
//                'type'=>1
//            ],
            [
                'name' => 'Водители',
                'web_type'=>2.1,
                'name_path'=>'pickup-driver',
                'type'=>1

            ],
            [
                'name' => 'Машины',
                'web_type'=>2.2,
                'name_path'=>'pickup-car',
                'type'=>1
            ],
            [
                'name' => 'Меню пользователя',
                'web_type'=>3.1,
                'name_path'=>'setting-menu_group',
                'type'=>1
            ],
//            [
//                'name' => 'Поля заявки',
//                'web_type'=>3.2,
//                'name_path'=>'setting-menu_app_field',
//                'type'=>1
//            ],
            [
                'name' => 'Срок доставки',
                'web_type'=>3.2,
                'name_path'=>'setting-config_time_list',
                'type'=>1
            ],
            [
                'name' => 'Филиалы',
                'web_type'=>3.3,
                'name_path'=>'branch',
                'type'=>1
            ],
            [
                'name' => 'Список сервис',
                'web_type'=>4.1,
                'name_path'=>'service-list',
                'type'=>1
            ],

            [
                'name' => 'Список ползаватели',
                'web_type'=>4.2,
                'name_path'=>'service-user_add',
                'type'=>1
            ],

            [
                'name' => 'Сервисный центр',
                'web_type'=>4.3,
                'name_path'=>'service',
                'type'=>1
            ],

            //menus for mobile
            [
                'name'=>'Склад',
                'app_type'=> 1.1,
                'type'=>2
            ],
            [
                'name'=>'Доставка',
                'app_type'=> 1.2,
                'type'=>2
            ],
            [
                'name'=>'Доставлено',
                'app_type'=> 1.3,
                'type'=>2
            ],
            [
                'name'=>'Склад',
                'app_type'=> 2.1,
                'type'=>2
            ],
            [
                'name'=>'Доставка',
                'app_type'=> 2.2,
                'type'=>2
            ],
            [
                'name'=>'Доставлено',
                'app_type'=> 2.3,
                'type'=>2
            ],
            [
                'name'=>'Входящие заявки от другого',
                'app_type'=> 3.1,
                'type'=>2
            ],
            [
                'name'=>'Входящие заявки',
                'app_type'=> 3.2,
                'type'=>2
            ],
            [
                'name'=>'Принято',
                'app_type'=> 3.3,
                'type'=>2
            ],
            [
                'name'=>'Оставлен в сервис центре',
                'app_type'=> 3.4,
                'type'=>2
            ],
            [
                'name'=>'Забрали из сервис центра',
                'app_type'=> 3.5,
                'type'=>2
            ],
            [
                'name'=>'Отправлен в магазин',
                'app_type'=> 3.6,
                'type'=>2
            ],
            [
                'name'=>'Заполненные заявки',
                'app_type'=> 3.7,
                'type'=>2
            ],
        ];
        $user_permissions = [
            [
                'role_id' => 1,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 1,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 1,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 1,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 1,
                'menu_id' => 10,
                'value' => 0,
            ],

            [
                'role_id' => 2,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 2,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 2,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 2,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 2,
                'menu_id' => 10,
                'value' => 0,
            ],


            [
                'role_id' => 3,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 3,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 3,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 3,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 3,
                'menu_id' => 10,
                'value' => 0,
            ],


            [
                'role_id' => 4,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 4,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 4,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 4,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 4,
                'menu_id' => 10,
                'value' => 0,
            ],

            [
                'role_id' => 5,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 5,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 5,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 5,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 5,
                'menu_id' => 10,
                'value' => 0,
            ],


            [
                'role_id' => 6,
                'menu_id' => 1,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 2,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 3,
                'value' => 0,
            ],
            [
                'role_id' => 6,
                'menu_id' => 4,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 5,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 6,
                'value' => 0,
            ],
            [
                'role_id' => 6,
                'menu_id' => 7,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 8,
                'value' => 1,
            ],
            [
                'role_id' => 6,
                'menu_id' => 9,
                'value' => 0,
            ],
            [
                'role_id' => 6,
                'menu_id' => 10,
                'value' => 0,
            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 11,
//                'value' => 1,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 12,
//                'value' => 0,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 13,
//                'value' => 0,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 14,
//                'value' => 1,
//            ]
//            ,
//            [
//                'role_id' => 4,
//                'menu_id' => 15,
//                'value' => 1,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 16,
//                'value' => 0,
//            ]
//            ,
//            [
//                'role_id' => 4,
//                'menu_id' => 17,
//                'value' => 1,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 18,
//                'value' => 0,
//            ]
//            ,
//            [
//                'role_id' => 4,
//                'menu_id' => 19,
//                'value' => 1,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 20,
//                'value' => 0,
//            ]
//            ,
//            [
//                'role_id' => 4,
//                'menu_id' => 21,
//                'value' => 1,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 22,
//                'value' => 0,
//            ],
//            [
//                'role_id' => 4,
//                'menu_id' => 23,
//                'value' => 0,
//            ]
        ];


        foreach ($menus as $key => $value) {
            Menus::create($value);
        }
        foreach ($role_lists as $key => $value) {
            RoleList::create($value);
        }
        foreach ($user_permissions as $key => $value) {
            UserPermission::create($value);
        }
        foreach ($users as $key => $value) {
            User::create($value);
        }

        foreach ($car_models as $key => $value) {
            CarModel::create($value);
        }

        foreach ($regions as $key => $value) {
            BranchRegion::create($value);
        }
        foreach ($config_time as $key => $value) {
            ConfigTime::create($value);
        }
    }
}
