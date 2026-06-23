// app/Observers/ApplicationObserver.php

class ApplicationObserver
{
  public function updated(Application $application): void
  {
    // getChanges() gives you ONLY the changed columns
    if (!empty($application->getChanges())) {
      AuditLog::create([
        'user_id'    => auth()->id(),
        'action'     => 'updated',
        'model_type' => Application::class,
        'model_id'   => $application->id,
        'old_values' => json_encode($application->getOriginal()),
        'new_values' => json_encode($application->getChanges()),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
      ]);
    }
  }
}

// Register in AppServiceProvider:
Application::observe(ApplicationObserver::class);
