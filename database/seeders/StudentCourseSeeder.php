<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Course;

class StudentCourseSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'Mihir Patel', 'email' => 'mihir@example.com'],
            ['name' => 'Emma Johnson', 'email' => 'emma@example.com'],
            ['name' => 'Alex Chen', 'email' => 'alex@example.com'],
            ['name' => 'Sarah Williams', 'email' => 'sarah@example.com'],
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Lisa Ray', 'email' => 'lisa@example.com'],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $courses = [
            ['title' => 'Laravel Advanced', 'description' => 'Master Laravel with advanced techniques'],
            ['title' => 'PHP Fundamentals', 'description' => 'Learn PHP from scratch'],
            ['title' => 'Vue.js with Laravel', 'description' => 'Build modern SPAs with Vue and Laravel'],
            ['title' => 'Database Design', 'description' => 'Learn relational database design principles'],
            ['title' => 'API Development', 'description' => 'Build RESTful APIs with Laravel'],
            ['title' => 'JavaScript ES6+', 'description' => 'Modern JavaScript features'],
            ['title' => 'DevOps for Laravel', 'description' => 'Deployment and CI/CD'],
            ['title' => 'Testing in Laravel', 'description' => 'PHPUnit and Pest testing'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }

        // Enroll students in random courses
        $allStudents = Student::all();
        $allCourses = Course::all();

        foreach ($allStudents as $student) {
            $randomCourses = $allCourses->random(rand(3, 6));
            foreach ($randomCourses as $course) {
                $student->courses()->attach($course->id, [
                    'enrolled_at' => now()->subDays(rand(1, 90))
                ]);
            }
        }
    }
}