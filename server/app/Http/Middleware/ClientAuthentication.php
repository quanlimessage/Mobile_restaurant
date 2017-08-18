<?php

namespace App\Http\Middleware;

use Closure;
use App\Helper\rsa;
use Response;
use App\Models\Tables;
use Doctrine\ORM\EntityManagerInterface;

class ClientAuthentication
{
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*$rsa = new rsa();

        $encode_time = 0;
        if( $request->header('endcode') ){
            $encode_time = $request->header('endcode');
        }
        
        $mac_address = 0;
        if( $request->header('tokenmac') ){
            $mac_address = $request->header('tokenmac');
        }

        $decode_time = $rsa->decode($encode_time);

        if( (time() - $decode_time) > 20 ){
            return response::json("Xac thuc fail roi ban ahihi", 401);
        }

        $findByMacaddress = $this->em->getRepository('App\Models\Tables')->findBy(array('mac_address' => $mac_address));
        if(empty($findByMacaddress)){
            return response::json("Xac thuc fail roi ban ahihi", 401);
        }*/

        return $next($request);
    }
}
