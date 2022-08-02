<?php
  
namespace App\Http\Controllers\Auth;
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Redis;
use Redirect;
  
class AuthController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('auth.login');
    }  
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function registration()
    {
        return view('auth.registration');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
   
        $credentials = $request->only('email', 'password');
        // dd($credentials['email']);

        $cachedBlog = Redis::get('userCred_' . $request['email']);

        if(isset($cachedBlog)) {
            $blog = json_decode($cachedBlog, FALSE);
            Auth::attempt($credentials);
            return redirect()->intended('dashboard')
                        ->withSuccess('You have Successfully loggedin from (redis)');
            // return response()->json([
            //             'status_code' => 201,
            //             'message' => 'Fetched from redis',
            //             'data' => $blog,
            //         ]);
            // return redirect()->intended('dashboard')
            //             ->withSuccess('You have Successfully loggedin');

            // return response()->json([
            //     'status_code' => 201,
            //     'message' => 'Fetched from redis',
            //     'data' => $blog,
            // ]);
        }else if (Auth::attempt($credentials)) {
            Redis::set('userCred_' . $credentials['email'], $credentials['password']);
            // return response()->json([
            //             'status_code' => 201,
            //             'message' => 'Fetched from database',
            //             // 'data' => $blog,
            //         ]);
            return redirect()->intended('dashboard')
                        ->withSuccess('You have Successfully loggedin from (Database)');
        }
        // {
        //     $blog = Blog::find($id);
        //     Redis::set('blog_' . $id, $blog);

        //     return response()->json([
        //         'status_code' => 201,
        //         'message' => 'Fetched from database',
        //         'data' => $blog,
        //     ]);
        // }

        // if (Auth::attempt($credentials)) {
        //     return redirect()->intended('dashboard')
        //                 ->withSuccess('You have Successfully loggedin');
        // }
  
        return redirect("login")->withSuccess('Oppes! You have entered invalid credentials');
    }
      
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postRegistration(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
           
        $data = $request->all();
        $check = $this->create($data);
         
        return redirect("login")->withSuccess('Great! Registration complete Successfully ');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function dashboard()
    {
        if (Auth::check()){
            return view('dashboard');
        }
  
        return redirect("login")->withSuccess('Opps! You do not have access');
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function logout() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }
}