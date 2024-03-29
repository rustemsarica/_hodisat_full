<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Mail\SupportMailManager;
use Mail;
use App\Http\Resources\V2\SupportCollection;
use App\Models\Support;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(9);
        return view('frontend.user.support_ticket.index', compact('tickets'));
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
        //dd();
        $ticket = new Ticket;
        $ticket->code = max(100000, (Ticket::latest()->first() != null ? Ticket::latest()->first()->code + 1 : 0)).date('s');
        $ticket->user_id = auth()->user()->id;
        $ticket->subject = $request->subject;
        $ticket->details = $request->details;
        $ticket->files = $request->attachments;

        if($ticket->save()){
            $this->send_support_mail_to_admin($ticket);
            return response()->json(['status' => true, 'message'=> translate('Ticket has been sent successfully')]);
        }
        else{
            return response()->json(['status' => false, 'message'=> translate('Something went wrong')]);
        }


    }

    public function send_support_mail_to_admin($ticket){
        $array['view'] = 'emails.support';
        $array['subject'] = 'Support ticket Code is:- '.$ticket->code;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Hi. A ticket has been created. Please check the ticket.';
        $array['link'] = route('support_ticket.admin_show', encrypt($ticket->id));
        $array['sender'] = $ticket->user->name;
        $array['details'] = $ticket->details;

        // dd($array);
        // dd(User::where('user_type', 'admin')->first()->email);
        try {
            Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new SupportMailManager($array));
        } catch (\Exception $e) {
            // dd($e->getMessage());
        }
    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = Ticket::findOrFail(decrypt($id));
        $ticket->client_viewed = 1;
        $ticket->save();
        $ticket_replies = $ticket->ticketreplies;
        return view('frontend.user.support_ticket.show', compact('ticket','ticket_replies'));
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function supports(Request $request)
    {
        if($request->parent_id>0){
            return new SupportCollection(Support::where('id',$request->parent_id)->orWhere('parent_id',$request->parent_id)->get());
        }else{
           return new SupportCollection(Support::where('parent_id',0)->get());
        }

    }
}
