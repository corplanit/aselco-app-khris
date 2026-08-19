<?php

use App\Http\Controllers\AccountLinkController;
use App\Http\Controllers\BillingApiController;
use App\Http\Controllers\BillingUploadController;
use App\Http\Controllers\Chats\ConversationController;
use App\Http\Controllers\Chats\MessagesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerComplaintController;
use App\Http\Controllers\FileManagerController;
use App\Models\TAccountRaw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\blogController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\postController;
use App\Http\Controllers\UserController;
use App\Models\postModel;
use App\Models\User;

use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\pagesController;
use App\Models\FileManager;

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::get('/blog/{id}/{slug}', function ($id, $slug) {
    return view('home.blog.index', compact('id', 'slug'));
});

Route::get('/storage/{link}', function ($link) {
    return view('home.components.storage', compact('link'));
});

use App\Http\Controllers\SuppChatController;
use App\Http\Controllers\SuppMessageController;

Route::middleware(['auth'])->group(function () {
    // UI
    Route::get('/supp/chat', [SuppChatController::class, 'index'])->name('supp.chat');

    // customer: ensure their conversation exists
    Route::post('/supp/chat/ensure-mine', [SuppChatController::class, 'ensureMine'])->name('supp.chat.ensure');

    // messages
    Route::get('/supp/chat/{conversation}/messages', [SuppMessageController::class, 'messages']);
    Route::post('/supp/chat/{conversation}/messages', [SuppMessageController::class, 'send']);
    Route::post('/supp/chat/{conversation}/read', [SuppMessageController::class, 'read']);

    Route::get('/supp/unread-total', [SuppMessageController::class, 'unreadTotal']);
});

// Route::get('/login', function () {
//     if (Auth::check()) {
//         return redirect('/dashboard');
//     }

//     return redirect('/');
// })->name('login');

Route::prefix('/page')->group(function () {
    Route::get('/about-us', function () {
        return view('home.pages.about-us');
    });

    Route::get('/history', function () {
        return view('home.pages.history');
    });
});

Route::get('/temp/logout', function (Request $request) {
    Auth::logout(); // Log out the user

    $request->session()->invalidate(); // Invalidate the session
    $request->session()->regenerateToken(); // Regenerate CSRF token

    return redirect('/')->with('success', 'You have been logged out.');
});

// Show the verification notice
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})
    ->middleware(['auth'])
    ->name('verification.notice');

// Handle the email verification link click
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard'); // or wherever you want after verification
})
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// Resend the verification email
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::middleware(['auth:sanctum', config('jetstream.auth_session', 'verified')])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role == 'staff') {
            return redirect('/t/dashboard');
        } else {
            return redirect('/u/dashboard');
        }
    });

    Route::middleware('auth')->group(function () {
        Route::get('/chats', [ConversationController::class, 'index'])->name('chats.show');
        Route::get('/chats/monitor', [ConversationController::class, 'monitor'])->name('chats.monitor');
        Route::get('/chats/monitor/{id}', [ConversationController::class, 'show'])->name('conversations.show');
    });
    Route::middleware(['auth'])->group(function () {
        // Create new DM / Group (used by the "New Chat" modal)
        Route::post('/chats/conversations', [ConversationController::class, 'store'])->name('chats.conversations.store');

        // Rename / delete conversation (group)
        Route::put('/chats/{conversation}', [ConversationController::class, 'update'])->name('chats.conversations.update');
        Route::delete('/chats/{conversation}', [ConversationController::class, 'destroy'])->name('chats.conversations.destroy');

        // Members management (group only)
        Route::post('/chats/{conversation}/members', [ConversationController::class, 'addMember'])->name('chats.conversations.members.add');
        Route::delete('/chats/{conversation}/members/{user}', [ConversationController::class, 'removeMember'])->name('chats.conversations.members.remove');

        // Mark as read (used by JS: axios.post(`/chats/${convId}/read`))
        Route::post('/chats/{conversation}/read', [ConversationController::class, 'markAsRead'])->name('chats.read');

        // Messages API
        Route::get('/chats/{conversation}/messages', [MessagesController::class, 'index'])->name('chats.messages.index');

        Route::post('/chats/{conversation}/messages', [MessagesController::class, 'store'])->name('chats.messages.store');

        Route::post('/chats/{conversation}/messages', [MessagesController::class, 'store'])->name('chats.messages.store');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/calendar', [CalendarEventController::class, 'view'])->name('calendar.view');
        Route::get('/calendar-events', [CalendarEventController::class, 'index']);
        Route::post('/calendar-events', [CalendarEventController::class, 'store']);
        Route::put('/calendar-events/{event}', [CalendarEventController::class, 'update']);
        Route::delete('/calendar-events/{event}', [CalendarEventController::class, 'destroy']);
    });

    Route::get('/t/dashboard', [BillingUploadController::class, 'dashboard']);
    Route::get('/u/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('/admin')->group(function () {
        route::get('/news-and-events', function () {
            return view('pages.admin.cms.news-and-events');
        });

        Route::get('/new-blog', [blogController::class, 'create']);
    });

    Route::prefix('/customer')->group(function () {
        Route::get('/registration', [CustomerController::class, 'create']);
        Route::post('/store', [AccountLinkController::class, 'store'])->name('customer.store');
        Route::post('/complaints/update-status', [CustomerComplaintController::class, 'updateStatus'])->name('complaints.update');
        Route::post('/api/bill', [BillingUploadController::class, 'list'])->name('api.bill');

        Route::post('/api/complaints', [CustomerComplaintController::class, 'list'])->name('api.complaints');
        Route::post('/api/complaints/all', [CustomerComplaintController::class, 'list_all'])->name('api.complaints.admin');
    });

    Route::prefix('/consumer')->group(function () {
        Route::get('/list', [CustomerController::class, 'index']);
        Route::post('/store', [AccountLinkController::class, 'store'])->name('link.store');
        Route::post('/update', [AccountLinkController::class, 'update'])->name('link.update');

        Route::post('/api/list', [CustomerController::class, 'list'])->name('api.consumer');
        Route::post('/account/link/create', [CustomerController::class, 'createAccountForRaw'])->name('account.link.create');
    });

    Route::post('/delete', function (Request $request) {
        $id = $request->id;
        $type = $request->type;

        if ($type == 'consumer') {
            TAccountRaw::where('id', $id)->update(['isDeleted' => 1]);
        } elseif ($type == 'post') {
            postModel::where('post_id', $id)->update(['isDeleted' => 1]);
        }
    })
        ->name('trash.bin')
        ->middleware('auth');

    Route::post('/user/search', function (Request $request) {
        $user = User::where('email', $request->email)->first();
        return response()->json(['user' => $user]);
    })->name('user.search');

    Route::get('/accounts/user/{user_id}', function ($user_id) {
        $accounts = TAccountRaw::where('user_id', $user_id)->get();
        return response()->json($accounts);
    });
    Route::post('/account/link/existing', [CustomerController::class, 'linkToExistingUser'])->name('account.link.existing');
    Route::post('/account/update', [CustomerController::class, 'updateAccount'])->name('account.update');
    Route::post('/account/password/change', [CustomerController::class, 'changePassword'])->name('account.password.change');
    Route::post('/account/password/reset', [CustomerController::class, 'resetPassword'])->name('account.password.reset');
    Route::post('/account/register', [CustomerController::class, 'store'])->name('account.register');

    Route::get('/validation', [BillingUploadController::class, 'validation']);
    Route::get('/api/account/validation', [BillingUploadController::class, 'accounts'])->name('api.account.validation');
    Route::get('/api/account/validation/all', [BillingUploadController::class, 'accounts_all'])->name('api.account.validation.all');

    Route::get('/billing-upload', [BillingUploadController::class, 'create'])->name('billing.upload.create');
    Route::post('/billing-upload', [BillingUploadController::class, 'store'])->name('billing.upload.store');

    Route::prefix('/link')->group(function () {
        Route::post('/store', [AccountLinkController::class, 'store'])->name('link.store');
        Route::post('/update', [AccountLinkController::class, 'update'])->name('link.update');
    });

    // Route::get('/chats', function () {
    //     return view('chats.index');
    // });

    Route::get('/support', function () {
        return view('chats.support');
    });

    Route::get('/complaint', function () {
        return view('pages.staff.complaint');
    });

    Route::get('/api/user-name/{id}', function ($id) {
        $user = User::find($id);
        return response()->json([
            'name' => $user?->name,
            'avatar' => $user?->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
        ]);
    });

    Route::get('/complaints/create', [CustomerComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints/store', [CustomerComplaintController::class, 'store'])->name('complaints.store');

    Route::get('/pages', [pagesController::class, 'index']);
    Route::get('/pages/new', [pagesController::class, 'create']);

    Route::get('/ublog', [postController::class, 'index']);
    Route::get('/ublog/new', [postController::class, 'create']);
    Route::get('/ublog/edit/{id}', [postController::class, 'edit']);
    Route::post('/ublog/save', [postController::class, 'save'])->name('blog.save');
    Route::put('/ublog/update/{id}', [postController::class, 'update'])->name('blog.update');
    Route::post('/ublog/remove-pdf', [postController::class, 'removePdf'])->name('blog.remove_pdf');
    Route::get('/api/blogs/list', [postController::class, 'datatable'])->name('api.blogs.list');

    Route::post('/fetch-billing', [BillingApiController::class, 'getBillingData']);

    Route::get('users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store'])->name('users.save');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/api/users', [UserController::class, 'datatable'])->name('api.users');

    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('menus/{menu}/builder', [MenuController::class, 'builder'])->name('menus.builder');
    Route::patch('menus/{menu}/builder', [MenuController::class, 'update'])->name('menus.builder');

    // Existing endpoints from earlier
    Route::resource('menus', MenuController::class)
        ->only(['store', 'show', 'update', 'destroy', 'create'])
        ->middleware([]);

    // Menu item CRUD
    Route::post('menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
    Route::patch('menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
    Route::delete('menu-items/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-items.destroy');

    // NEW: save full tree (parent_id + order)
    Route::post('menu-items/save-tree', [MenuItemController::class, 'saveTree'])->name('menu-items.saveTree');

    Route::get('pages', [MenuController::class, 'pages'])->name('menus.index');
    Route::post('/api/pages/list', [MenuController::class, 'api_pages'])->name('api.pages');

    Route::prefix('/file-manager')->group(function () {
        Route::get('/list', [FileManagerController::class, 'index'])->name('filemanager.index');
        Route::get('/list/folder', [FileManagerController::class, 'index'])->name('filemanager.index');
        Route::get('/storage', [FileManagerController::class, 'index'])->name('filemanager.index');
        Route::get('/{parent_id?}', [FileManagerController::class, 'xindex'])->name('filemanager.index');
        Route::get('/folder/privacy', [FileManagerController::class, 'updatePrivacy'])->name('folder.privacy');
        Route::get('/folder/file/{url}', [FileManagerController::class, 'preview'])->name('folder.preview.file');

        Route::post('/upload', [FileManagerController::class, 'store'])->name('filemanager.store');
        Route::post('/create/folder', [FileManagerController::class, 'folder'])->name('filemanager.folder');
        Route::post('/submit', [FileManagerController::class, 'submitted'])->name('filemanager.submitted');
        Route::post('/rename/{id}', [FileManagerController::class, 'rename'])->name('filemanager.rename');

        Route::post('/api/files', [FileManagerController::class, 'api_files'])->name('api.filemanager.files');

        Route::delete('/{file}', [FileManagerController::class, 'destroy'])->name('filemanager.destroy');
    });

    Route::get('/file-manager/v2/list', [FileManagerController::class, 'index_v2']); // demo page
    Route::post('/drive/file/upload-chunk', [ChunkUploadController::class, 'chunk'])->name('api.drive.file.upload-chunk');
    // ---------------- Drive uploads
    Route::get('/google-drive', fn(Request $r) => view('pages.filemanager.upload'));
    Route::post('/google-drive-actived', [GoogleDriveController::class, 'activated'])->name('drive.folder.activated');
    Route::post('/drive/folder/create', [GoogleDriveController::class, 'folder'])->name('drive.folder.create');
    Route::get('/drive/storage', [GoogleDriveController::class, 'getStorageInfo']);
    Route::post('/drive/file/upload', [GoogleDriveController::class, 'upload'])->name('drive.file.upload');

    Route::post('/delete', function (Request $request) {
        $id = $request->id;
        $type = $request->type;

        FileManager::where('id', $id)->update(['isDeleted' => 1]);
    })->name('trash.bin');
});
