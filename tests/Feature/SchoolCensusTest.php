<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolCensusTest extends TestCase
{
    use RefreshDatabase;

    private SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-20');
        $this->session = SchoolSession::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function classNamed(string $name): Section
    {
        return Section::factory()->create([
            'class_id' => SchoolClass::factory()->create(['class_name' => $name, 'session_id' => $this->session->id])->id,
        ]);
    }

    private function enrol(Section $section, string $gender, ?string $birthday): void
    {
        Promotion::factory()->create([
            'student_id' => User::factory()->create(['role' => 'student', 'gender' => $gender, 'birthday' => $birthday])->id,
            'section_id' => $section->id,
            'class_id' => $section->class_id,
            'session_id' => $this->session->id,
        ]);
    }

    private function seedSchool(): void
    {
        $primaryOne = $this->classNamed('Primary 1');
        $this->enrol($primaryOne, 'Male', '2020-03-01');
        $this->enrol($primaryOne, 'female', '2019-12-25');
        $this->enrol($primaryOne, 'Female', null);

        $primarySix = $this->classNamed('Primary 6');
        $this->enrol($primarySix, 'Male', '2014-01-10');

        User::factory()->create(['role' => 'teacher', 'gender' => 'Female']);
        User::factory()->create(['role' => 'teacher', 'gender' => 'Male']);
    }

    public function test_census_page_tallies_pupils_by_class_sex_and_age(): void
    {
        $this->seedSchool();

        $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs(User::factory()->create(['role' => User::ROLE_SECRETARY]))
            ->get(route('reports.census'))
            ->assertOk()
            ->assertSeeInOrder(['Primary 1', '1', '2', '3'])
            ->assertSeeInOrder(['Primary 6', '1', '0', '1'])
            ->assertSeeInOrder(['All classes', '2', '2', '4'])
            ->assertSee('1 pupil has no date of birth on file');
    }

    public function test_census_csv_has_class_rows_age_columns_and_teachers(): void
    {
        $this->seedSchool();

        $response = $this->withSession(['browse_session_id' => $this->session->id])
            ->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('reports.census.download'));

        $response->assertOk();
        $response->assertDownload('school-census-2026-11-20.csv');

        $rows = array_map('str_getcsv', array_values(array_filter(explode("\n", $response->streamedContent()))));
        $header = $rows[1];

        $this->assertSame(['Class', 'Boys', 'Girls', 'Total', 'Age Under 5', 'Age 5', 'Age 6'], array_slice($header, 0, 7));

        $primaryOne = array_combine($header, $rows[2]);
        $this->assertSame(['Primary 1', '1', '2', '3'], array_slice($rows[2], 0, 4));
        $this->assertSame('2', $primaryOne['Age 6']);
        $this->assertSame('0', $primaryOne['Age 5']);
        $this->assertSame('1', $primaryOne['Age Unknown']);

        $primarySix = array_combine($header, $rows[3]);
        $this->assertSame('1', $primarySix['Age 12']);

        $this->assertSame(['All teachers', '1', '1', '2'], end($rows));
    }

    public function test_teachers_and_students_cannot_see_the_census(): void
    {
        foreach (['teacher', 'student'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('reports.census'))
                ->assertForbidden();

            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('reports.census.download'))
                ->assertForbidden();
        }
    }
}
