<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Book;
use App\Models\BookTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use DB;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'success',
            'books'=>Book::limit(10)->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only('book_name','cover_image','author');
        $validator = Validator::make($data, [
            'book_name' => 'required|alpha_spaces|unique:books',
			'author' => 'required|alpha_spaces',
            'cover_image' => 'required|alpha_spaces|unique:books'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, add new book
        $book = Book::create([
        	'book_name' => $request->book_name,
			'author' => $request->author,
        	'cover_image' => $request->cover_image
        ]);

        return (!empty($book)) ? response()->json([
            'success' => true,
            'message' => 'Books Details Saved Successfully',
            'data' => $book
        ], Response::HTTP_OK) : response()->json([
            'success' => false,
            'message' => 'Books Not Details Saved Something Went Wrong',
            'data' => []
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function show($b_id)
    {   
        $book = Book::where('b_id',$b_id)->get();
        return (!empty($book[0])) ? response()->json([
            'success' => true,
            'message' => 'success',
            'books'=>$book
        ]) : response()->json([
            'success' => false,
            'message' => 'Books Details Not Found',
            'books'=>$book
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$b_id)
    {
        $data = $request->only('book_name','cover_image','author');
        $validator = Validator::make($data, [
            'book_name' => 'required|alpha_spaces|unique:books,book_name,'.$b_id.',b_id',
			'author' => 'required|alpha_spaces',
            'cover_image' => 'required|alpha_spaces|unique:books,cover_image,'.$b_id.',b_id',
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, update books details
        $book = Book::where('b_id',$b_id)->update([
        	'book_name' => $request->book_name,
			'author' => $request->author,
        	'cover_image' => $request->cover_image
        ]);

        return (!$book) ?  response()->json([
            'success' => false,
            'message' => 'Books Details Not Updated Something Went Wrong',
        ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
         response()->json([
            'success' => true,
            'message' => 'Books Details Updated Successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function destroy($b_id)
    {
        $flag = Book::where('b_id',$b_id)->delete();

        return (!$flag) ?  response()->json([
            'success' => false,
            'message' => 'Books Details Not Deleted Something Went Wrong',
        ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
         response()->json([
            'success' => true,
            'message' => 'Books Details Deleted Successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Book Rent function
     *
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function rentbook(Request $request,$b_id)
    {   
        $user = JWTAuth::authenticate($request->token);
        if(!empty($user) && !empty($user->u_id))
        {   
            $checkAvailability = BookTransaction::select('user_id')
            ->where('book_id',$b_id)
            ->where('type','rent')
            ->get();

            if(!empty($checkAvailability[0]->user_id))
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Books Already Rented!',
                ], Response::HTTP_OK);
            }
            else
            {   
                $data = $request->only('remark','type');
                $validator = Validator::make($data, [
                    'remark' => 'alpha_spaces'
                ]);

                //Send failed response if request is not valid
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->messages()], 200);
                }

                $checkBookId = Book::select('book_name')
                ->where('b_id',$b_id)
                ->first();
                if(!empty($checkBookId->book_name))
                {
                    $booktrasaction = BookTransaction::create([
                        'user_id'=>$user->u_id,
                        'book_id'=>$b_id,
                        'type'=>'rent',
                        'remark'=>(!empty($request->remark)) ? $request->remark:"",
                        'rent_date'=>date('Y-m-d'),
                        'return_date'=>null
                    ]);

                    return (empty($booktrasaction)) ?  response()->json([
                        'success' => false,
                        'message' => 'Something Went Wrong Unable to rent book please try again!',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
                    response()->json([
                        'success' => true,
                        'message' => 'Books Rented Successfully',
                    ], Response::HTTP_OK);
                }
                else
                {
                    return response()->json([
                        'success' => false,
                        'message' => 'Book With BookId '.$b_id.' is Not Exist!',
                    ], Response::HTTP_OK);
                }
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Logged User Details Are Missing Please Login and Try Again',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Book return function
     *
     * @param  int $bookid
     * @return \Illuminate\Http\Response
     */
    public function returnbook(Request $request,$b_id)
    {   
        $user = JWTAuth::authenticate($request->token);
        if(!empty($user) && !empty($user->u_id))
        {   
            $checkAvailability = BookTransaction::select('id')
            ->where('book_id',$b_id)
            ->where('user_id',$user->u_id)
            ->where('type','rent')
            ->get();

            if(empty($checkAvailability[0]->id))
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Books Not Rented By User So Can Not Return It!',
                ], Response::HTTP_OK);
            }
            else
            {   
                $data = $request->only('remark','type');
                $validator = Validator::make($data, [
                    'remark' => 'alpha_spaces'
                ]);

                //Send failed response if request is not valid
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->messages()], 200);
                }
                $booktrasaction = BookTransaction::where('id',$checkAvailability[0]->id)->update([
                    'type'=>'return',
                    'remark'=>(!empty($request->remark)) ? $request->remark:"",
                    'return_date'=>date('Y-m-d')
                ]);

                return (empty($booktrasaction)) ?  response()->json([
                    'success' => false,
                    'message' => 'Something Went Wrong Unable to return book please try again!',
                ], Response::HTTP_INTERNAL_SERVER_ERROR) :   
                response()->json([
                    'success' => true,
                    'message' => 'Books Returned Successfully',
                ], Response::HTTP_OK);
            }
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Logged User Details Are Missing Please Login and Try Again',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * User Wise Book Rented function
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getuserbooks(Request $request)
    {
        $user = JWTAuth::authenticate($request->token);
        if(!empty($user) && !empty($user->u_id))
        {
            $userBooks = DB::table('rel_user_books')
            ->select('remark','rent_date','users.firstname','users.lastname','books.book_name')
            ->join('users','rel_user_books.user_id','=','users.u_id')
            ->join('books','rel_user_books.book_id','=','books.b_id')
            ->where('rel_user_books.user_id',$user->u_id)
            ->where('rel_user_books.type','rent')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'success',
                'rented_books'=>$userBooks
            ], Response::HTTP_OK);

        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'Logged User Details Are Missing Please Login and Try Again',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
