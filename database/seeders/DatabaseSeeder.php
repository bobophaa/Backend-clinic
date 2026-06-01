<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. បង្កើតគណនី Users សាកល្បង
        $users = [
            ['name' => 'អ្នកគ្រប់គ្រង', 'email' => 'admin@clinic.com', 'role' => 'admin'],
            ['name' => 'វេជ្ជបណ្ឌិត', 'email' => 'doctor@clinic.com', 'role' => 'doctor'],
            ['name' => 'អ្នកជំងឺ', 'email' => 'patient@clinic.com', 'role' => 'patient'],
            ['name' => 'បុគ្គលិកទទួលភ្ញៀវ', 'email' => 'reception@clinic.com', 'role' => 'receptionist'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                ],
            );
        }

        // 2. រៀបចំទិន្នន័យគ្រូពេទ្យ (កែសម្រួលឈ្មោះ Column ឱ្យត្រូវនឹង Database របស់ប្អូន)
        $doctors = [
            ['name' => 'វេជ្ជ. សុខ រ័ត្ន', 'specialization' => 'បេះដូង', 'phone' => '012 345 678', 'room' => 'A-201', 'experience' => '12 ឆ្នាំ', 'fee' => '25.00', 'is_active' => true],
            ['name' => 'វេជ្ជ. ចាន់ណា', 'specialization' => 'ស្ត្រី និងសម្រាលកូន', 'phone' => '011 678 990', 'room' => 'B-101', 'experience' => '9 ឆ្នាំ', 'fee' => '30.00', 'is_active' => true],
            ['name' => 'វេជ្ជ. ដារ៉ា', 'specialization' => 'កុមារ', 'phone' => '099 887 766', 'room' => 'C-202', 'experience' => '6 ឆ្នាំ', 'fee' => '20.00', 'is_active' => false],
            ['name' => 'វេជ្ជ. ស្រីពៅ', 'specialization' => 'ស្បែក', 'phone' => '098 234 112', 'room' => 'D-401', 'experience' => '10 ឆ្នាំ', 'fee' => '22.00', 'is_active' => true],
            ['name' => 'វេជ្ជ. វណ្ណា', 'specialization' => 'ឆ្អឹង និងសន្លាក់', 'phone' => '097 556 882', 'room' => 'A-303', 'experience' => '15 ឆ្នាំ', 'fee' => '35.00', 'is_active' => true],
            ['name' => 'វេជ្ជ. រតនា', 'specialization' => 'ភ្នែក', 'phone' => '096 555 110', 'room' => 'E-101', 'experience' => '8 ឆ្នាំ', 'fee' => '18.00', 'is_active' => true],
            ['name' => 'វេជ្ជ. គឹម សុភា', 'specialization' => 'ពិនិត្យទូទៅ', 'phone' => '015 111 222', 'room' => 'G-101', 'experience' => '11 ឆ្នាំ', 'fee' => '15.00', 'is_active' => true],
        ];

        foreach ($doctors as $doctor) {
            $emailName = str_replace([' ', '.'], '', strtolower($doctor['name']));
            $emailMap = [
                'វេជ្ជ. សុខ រ័ត្ន' => 'sokrath@clinic.com',
                'វេជ្ជ. ចាន់ណា' => 'channa@clinic.com',
                'វេជ្ជ. ដារ៉ា' => 'dara@clinic.com',
                'វេជ្ជ. ស្រីពៅ' => 'sreypov@clinic.com',
                'វេជ្ជ. វណ្ណា' => 'vanna@clinic.com',
                'វេជ្ជ. រតនា' => 'ratana@clinic.com',
                'វេជ្ជ. គឹម សុភា' => 'kimsopha@clinic.com',
            ];
            
            $email = $emailMap[$doctor['name']] ?? ($emailName . '@clinic.com');
            
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $doctor['name'],
                    'role' => 'doctor',
                    'password' => Hash::make('password'),
                ]
            );
            
            $doctorData = $doctor;
            $doctorData['user_id'] = $user->id;
            
            Doctor::updateOrCreate(
                ['name' => $doctor['name']],
                $doctorData
            );
        }

        // 3. ចងភ្ជាប់គណនី 'doctor@clinic.com' ទៅឱ្យគ្រូពេទ្យពិនិត្យទូទៅ
        $doctorUser = User::where('email', 'doctor@clinic.com')->first();
        $generalDoctor = Doctor::where('specialization', 'ពិនិត្យទូទៅ')->first();
        
        if ($doctorUser && $generalDoctor) {
            $oldDoctor = Doctor::where('user_id', $doctorUser->id)->first();
            if ($oldDoctor && $oldDoctor->id !== $generalDoctor->id) {
                $generalDoctorUserId = $generalDoctor->user_id;
                DB::transaction(function () use ($oldDoctor, $generalDoctor, $doctorUser, $generalDoctorUserId) {
                    $oldDoctor->update(['user_id' => $generalDoctorUserId]);
                    $generalDoctor->update(['user_id' => $doctorUser->id]);
                });
            } else {
                $generalDoctor->update(['user_id' => $doctorUser->id]);
            }
        }
    }
}