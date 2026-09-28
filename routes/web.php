<?php

use App\Http\Controllers\Admin\LocalEducationUnitController;
use App\Http\Controllers\Admin\ScholarshipController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuardianProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ScholarshipController as PublicScholarshipController;
use App\Http\Controllers\SelectionController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/scholarships', [PublicScholarshipController::class, 'index'])->name('scholarships.index');
Route::get('/scholarships/{scholarship}', [PublicScholarshipController::class, 'show'])->whereNumber('scholarship')->name('scholarships.show');

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::middleware('role:student')->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [StudentProfileController::class, 'show'])->name('show');
        Route::get('/create', [StudentProfileController::class, 'create'])->name('create');
        Route::post('/', [StudentProfileController::class, 'store'])->name('store');
        Route::get('/edit', [StudentProfileController::class, 'edit'])->name('edit');
        Route::put('/', [StudentProfileController::class, 'update'])->name('update');
    });

    Route::middleware('role:guardian')->prefix('guardian')->name('guardian.')->group(function () {
        Route::get('/profile', [GuardianProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [GuardianProfileController::class, 'store'])->name('profile.store');
        Route::put('/profile', [GuardianProfileController::class, 'update'])->name('profile.update');
        Route::post('/students', [GuardianProfileController::class, 'linkStudent'])->name('students.link');
    });

    Route::middleware('role:student,guardian')->prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'index'])->name('index');
        Route::get('/create', [ApplicationController::class, 'create'])->name('create');
        Route::post('/', [ApplicationController::class, 'store'])->name('store');
        Route::get('/{application}', [ApplicationController::class, 'show'])->whereNumber('application')->name('show');
        Route::get('/{application}/edit', [ApplicationController::class, 'edit'])->whereNumber('application')->name('edit');
        Route::put('/{application}', [ApplicationController::class, 'update'])->whereNumber('application')->name('update');
        Route::patch('/{application}/submit', [ApplicationController::class, 'submit'])->whereNumber('application')->name('submit');
        Route::delete('/{application}', [ApplicationController::class, 'destroy'])->whereNumber('application')->name('destroy');
        Route::get('/{application}/documents', [ApplicationDocumentController::class, 'index'])->whereNumber('application')->name('documents.index');
        Route::post('/{application}/documents', [ApplicationDocumentController::class, 'store'])->whereNumber('application')->name('documents.store');
        Route::delete('/{application}/documents/{document}', [ApplicationDocumentController::class, 'destroy'])->whereNumber('application')->whereNumber('document')->name('documents.destroy');
    });

    Route::get(
        'applications/{application}/documents/{document}/download',
        [ApplicationDocumentController::class, 'download']
    )->whereNumber('application')->whereNumber('document')
        ->middleware('role:student,guardian,school_officer,local_officer,committee,admin')
        ->name('applications.documents.download');

    Route::middleware('role:school_officer,local_officer')->prefix('verifications')->name('verifications.')->group(function () {
        Route::get('/', [VerificationController::class, 'index'])->name('index');
        Route::get('/{application}', [VerificationController::class, 'show'])->whereNumber('application')->name('show');
        Route::patch('/{application}/start', [VerificationController::class, 'start'])->whereNumber('application')->name('start');
        Route::patch('/{application}/approve', [VerificationController::class, 'approve'])->whereNumber('application')->name('approve');
        Route::patch('/{application}/return', [VerificationController::class, 'returnForCorrection'])->whereNumber('application')->name('return');
    });

    Route::middleware('role:committee')->prefix('selection')->name('selection.')->group(function () {
        Route::get('/', [SelectionController::class, 'index'])->name('index');
        Route::get('/{application}', [SelectionController::class, 'show'])->whereNumber('application')->name('show');
        Route::patch('/{application}/scores', [SelectionController::class, 'scores'])->whereNumber('application')->name('scores');
        Route::patch('/{application}/decision', [SelectionController::class, 'decide'])->whereNumber('application')->name('decision');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('scholarships', [PublicScholarshipController::class, 'index'])->name('scholarships.index');
        Route::get('scholarships/create', [ScholarshipController::class, 'create'])->name('scholarships.create');
        Route::post('scholarships', [ScholarshipController::class, 'store'])->name('scholarships.store');
        Route::get('scholarships/{scholarship}/edit', [ScholarshipController::class, 'edit'])->name('scholarships.edit');
        Route::put('scholarships/{scholarship}', [ScholarshipController::class, 'update'])->name('scholarships.update');
        Route::patch('scholarships/{scholarship}/status', [ScholarshipController::class, 'updateStatus'])->name('scholarships.status');

        Route::get('schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');

        Route::get('local-education-units', [LocalEducationUnitController::class, 'index'])->name('local-education-units.index');
        Route::get('local-education-units/create', [LocalEducationUnitController::class, 'create'])->name('local-education-units.create');
        Route::post('local-education-units', [LocalEducationUnitController::class, 'store'])->name('local-education-units.store');
        Route::get('local-education-units/{unit}/edit', [LocalEducationUnitController::class, 'edit'])->name('local-education-units.edit');
        Route::put('local-education-units/{unit}', [LocalEducationUnitController::class, 'update'])->name('local-education-units.update');
    });
});
