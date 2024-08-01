use App\Http\Controllers\DataController;

Route::get('/data', [DataController::class, 'getData']);
