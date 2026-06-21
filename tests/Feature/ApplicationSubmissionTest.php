// tests/Feature/ApplicationSubmissionTest.php

class ApplicationSubmissionTest extends TestCase
{
  use RefreshDatabase;   // Resets DB after each test

  public function test_applicant_can_submit_application_to_published_vacancy(): void
  {
    // Arrange
    $applicant = User::factory()->applicant()->create();
    $vacancy   = Vacancy::factory()->published()->create();

    // Act
    $response = $this->actingAs($applicant)     // Simulate logged-in user
      ->postJson('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'personal'   => [...],
      ]);

    // Assert
    $response->assertCreated();                 // HTTP 201
    $response->assertJsonPath('data.status', 'submitted');
    $this->assertDatabaseHas('applications', [
      'vacancy_id'   => $vacancy->id,
      'applicant_id' => $applicant->profile->id,
      'status'       => 'submitted',
    ]);
    Notification::assertSentTo($applicant, ApplicationSubmitted::class);
  }

  public function test_hr_officer_cannot_submit_application(): void
  {
    $hrOfficer = User::factory()->hrOfficer()->create();
    $vacancy   = Vacancy::factory()->published()->create();

    $this->actingAs($hrOfficer)
      ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
      ->assertForbidden();   // HTTP 403
  }
}
