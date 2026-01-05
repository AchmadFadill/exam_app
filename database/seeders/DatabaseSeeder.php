<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Subjects
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
            ['name' => 'Biologi', 'code' => 'BIO'],
            ['name' => 'Sejarah', 'code' => 'SEJ'],
            ['name' => 'Geografi', 'code' => 'GEO'],
        ];
        foreach ($subjects as $s) Subject::create($s);

        // Classrooms
        $classrooms = [
            ['name' => 'X IPA 1', 'level' => 'X'],
            ['name' => 'X IPA 2', 'level' => 'X'],
            ['name' => 'X IPS 1', 'level' => 'X'],
            ['name' => 'XI IPA 1', 'level' => 'XI'],
            ['name' => 'XI IPA 2', 'level' => 'XI'],
            ['name' => 'XI IPS 1', 'level' => 'XI'],
            ['name' => 'XII IPA 1', 'level' => 'XII'],
            ['name' => 'XII IPA 2', 'level' => 'XII'],
            ['name' => 'XII IPS 1', 'level' => 'XII'],
        ];
        foreach ($classrooms as $c) Classroom::create($c);

        // Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@smait-baitulmuslim.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Teachers
        $teachers = [
            ['name' => 'Bu Siti Matematika', 'email' => 'siti@smait-baitulmuslim.sch.id', 'subject_id' => 1],
            ['name' => 'Pak Ahmad Fisika', 'email' => 'ahmad@smait-baitulmuslim.sch.id', 'subject_id' => 4],
            ['name' => 'Bu Fatimah Biologi', 'email' => 'fatimah@smait-baitulmuslim.sch.id', 'subject_id' => 6],
        ];
        foreach ($teachers as $t) {
            $user = User::create(['name' => $t['name'], 'email' => $t['email'], 'password' => Hash::make('password'), 'role' => 'teacher']);
            Teacher::create(['user_id' => $user->id, 'nip' => '19' . rand(70, 99) . rand(10000000, 99999999), 'subject_id' => $t['subject_id']]);
        }

        // Students
        $students = [
            ['name' => 'Ahmad Siswa', 'nis' => '2024001', 'classroom_id' => 1],
            ['name' => 'Budi Santoso', 'nis' => '2024002', 'classroom_id' => 1],
            ['name' => 'Citra Dewi', 'nis' => '2024003', 'classroom_id' => 1],
            ['name' => 'Dian Pertiwi', 'nis' => '2024004', 'classroom_id' => 2],
            ['name' => 'Eko Prasetyo', 'nis' => '2024005', 'classroom_id' => 2],
        ];
        foreach ($students as $s) {
            $user = User::create(['name' => $s['name'], 'email' => strtolower(str_replace(' ', '.', $s['name'])) . '@siswa.smait-baitulmuslim.sch.id', 'password' => Hash::make($s['nis']), 'role' => 'student']);
            Student::create(['user_id' => $user->id, 'nis' => $s['nis'], 'classroom_id' => $s['classroom_id']]);
        }

        // Sample Questions
        $mathTeacher = Teacher::first();
        $mathSubject = Subject::where('code', 'MTK')->first();

        $questions = [
            ['text' => '<p>Jika seorang pedagang membeli barang seharga Rp 100.000 dan menjualnya dengan harga Rp 120.000, berapakah persentase keuntungannya?</p>', 'options' => [['A', '10%', false], ['B', '15%', false], ['C', '20%', true], ['D', '25%', false], ['E', '30%', false]], 'explanation' => 'Keuntungan = 20.000, Persentase = 20%'],
            ['text' => '<p>Himpunan penyelesaian dari persamaan 2x + 5 = 11 adalah...</p>', 'options' => [['A', '{2}', false], ['B', '{3}', true], ['C', '{4}', false], ['D', '{5}', false], ['E', '{6}', false]], 'explanation' => '2x = 6, x = 3'],
            ['text' => '<p>Salah satu akar persamaan kuadrat x² - 5x + 6 = 0 adalah...</p>', 'options' => [['A', '1', false], ['B', '2', true], ['C', '4', false], ['D', '5', false], ['E', '6', false]], 'explanation' => '(x-2)(x-3) = 0'],
            ['text' => '<p>Nilai dari 2⁵ adalah...</p>', 'options' => [['A', '16', false], ['B', '25', false], ['C', '32', true], ['D', '64', false], ['E', '128', false]], 'explanation' => '2⁵ = 32'],
        ];

        foreach ($questions as $q) {
            $question = Question::create(['teacher_id' => $mathTeacher->id, 'subject_id' => $mathSubject->id, 'type' => 'multiple_choice', 'text' => $q['text'], 'explanation' => $q['explanation']]);
            foreach ($q['options'] as $o) {
                QuestionOption::create(['question_id' => $question->id, 'label' => $o[0], 'text' => $o[1], 'is_correct' => $o[2]]);
            }
        }

        // Sample Exam
        $exam = Exam::create([
            'teacher_id' => $mathTeacher->id, 'subject_id' => $mathSubject->id, 'name' => 'Ulangan Harian Matematika Bab 1',
            'date' => now()->addDays(1)->toDateString(), 'start_time' => '08:00:00', 'end_time' => '09:30:00',
            'duration_minutes' => 90, 'token' => Exam::generateToken(), 'passing_grade' => 70, 'default_score' => 25,
            'shuffle_questions' => true, 'shuffle_answers' => false, 'tab_tolerance' => 3, 'status' => 'scheduled',
        ]);
        $exam->classrooms()->attach([1, 2]);
        foreach (Question::all() as $i => $q) ExamQuestion::create(['exam_id' => $exam->id, 'question_id' => $q->id, 'order' => $i + 1, 'score' => 25]);

        $this->command->info('Database seeded! Token: ' . $exam->token);
    }
}
