<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Plan;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FrontEndController extends Controller
{
    public function __construct()
    {
        // Set theme in session if not already set
        // $this->middleware(function ($request, $next) {
        //     if (!session()->has('active_theme')) {
        //         session(['active_theme' => $this->getActiveTheme()]);
        //     }
        //     return $next($request);
        // });
    }

    private function getActiveTheme()
    {
        return ThemeSetting::getActiveTheme();
    }

    public function index()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $plans = Plan::where('status', 'active')->orderBy('monthly_price', 'asc')->take(3)->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        $theme = $this->getActiveTheme();
        
        // Return different views based on theme
        if ($theme === 'old') {
            return view('front-end.old.index', [
                'plans' => $plans,
                'currency_symbol' => $currency_symbol,
                'theme' => $theme
            ]);
        }

        // For new theme, get all data needed for single page
        $allPlans = Plan::where('status', 'active')->orderBy('monthly_price', 'asc')->get();

        return view('front-end.index', [
            'plans' => $plans,
            'all_plans' => $allPlans,
            'currency_symbol' => $currency_symbol,
            'theme' => $theme
        ]);
    }

    public function features()
    {
        $theme = $this->getActiveTheme();

        if ($theme === 'old') {
            return view('front-end.old.features', ['theme' => $theme]);
        }

        return view('front-end.features', ['theme' => $theme]);
    }

    public function about_us()
    {
        $theme = $this->getActiveTheme();

        if ($theme === 'new') {
            // Redirect to homepage with about anchor
            return redirect()->route('frontend.index') . '#about-us';
        }

        return view('front-end.about_us', ['theme' => $theme]);
    }

    public function contact_us()
    {
        $theme = $this->getActiveTheme();

        if ($theme === 'new') {
            // Redirect to homepage with contact anchor
            return redirect()->route('frontend.index') . '#contact-us';
        }

        return view('front-end.contact_us', ['theme' => $theme]);
    }

   public function pricing()
    {
        $plans = Plan::where('status', 'active')->orderBy('monthly_price', 'asc')->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        $theme = $this->getActiveTheme();

        if ($theme === 'old') {
            return view('front-end.old.pricing', [
            'currency_symbol' => $currency_symbol,
            'plans' => $plans,
            'theme' => $theme
        ]);
        }

        return view('front-end.pricing', [
            'currency_symbol' => $currency_symbol,
            'plans' => $plans,
            'theme' => $theme
        ]);
    }
    public function send_mail(Request $request)
    {
        // Validate form data (optional but recommended)
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string'
        ]);

        // Prepare email content
        $senderName = $request->input('name');
        $senderEmail = $request->input('email');
        $messageContent = $request->input('message');

        $emailBody = [
            'name' => $senderName,
            'email' => $senderEmail,
            'message' => $messageContent
        ];

        try {
            // Send the email using the globally configured settings
            Mail::send('emails.contact', ['content' => $emailBody], function ($message) use ($senderEmail, $senderName) {
                // Use the globally configured "from" and set reply-to as sender's email
                $message->to(config('mail.from.address'))->subject("[Contact Form] Inquiry from $senderName");
                $message->replyTo($senderEmail, $senderName);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!'
            ]);
        } catch (Exception $e) {
            // Error response with exception message
            return response()->json([
                'success' => false,
                'message' => "Message could not be sent. Mailer Error: {$e->getMessage()}"
            ], 500); // Set appropriate status code for internal server error
        }
    }

    // public function pricing()
    // {
    //     $plans = Plan::where('status', 'active')->orderBy('monthly_price', 'asc')->get();
    //     $currency_symbol = (get_settings('general_settings')['currency_symbol']);
    //     return view('front-end.pricing', ['currency_symbol' => $currency_symbol, 'plans' => $plans]);
    // }
    public function faqs()
    {
        return view('front-end.faqs');
    }
    public function terms_and_condition()
    {
        $terms_and_conditions = get_settings('terms_and_conditions');
        return view('front-end.terms_and_conditions', ['terms_and_conditions' => $terms_and_conditions]);
    }
    public function refund_policy()
    {
        $refund_policy = get_settings('refund_policy');
        return view('front-end.refund_policy', ['refund_policy' => $refund_policy]);
    }
    public function privacy_policy()
    {
        $privacy_policy = get_settings('privacy_policy');
        return view('front-end.privacy_policy', ['privacy_policy' => $privacy_policy]);
    }

    public function switchTheme(Request $request)
    {
        $theme = $request->input('theme', 'new');

        if (!in_array($theme, ['old', 'new'])) {
            return response()->json(['error' => 'Invalid theme'], 400);
        }

        ThemeSetting::setActiveTheme($theme);

        // Store in session as well for immediate effect
        session(['active_theme' => $theme]);

        return response()->json(['success' => true, 'theme' => $theme]);
    }

    public function storeThemeSettings(Request $request)
    {
        $request->validate([
            'active_theme' => 'required|in:old,new',
        ]);

        $theme = $request->input('active_theme','new');

        try {
            // Set the active theme using your existing ThemeSetting model
            ThemeSetting::setActiveTheme($theme);

            // Store in session as well for immediate effect
            session(['active_theme' => $theme]);


            return response()->json([
                'error' => false,
                'message' => get_label('theme_updated_successfully', 'Theme updated successfully'),
                'id' => '',
                'data' => [
                    'active_theme' => $theme
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => get_label('theme_update_failed', 'Failed to update theme: ') . $e->getMessage()
            ], 500);
        }
    }
}
