<?php

use Illuminate\Database\Seeder;
use \App\User;
class UsersSeeder extends Seeder
{


    private $numberOfAccounts = 20;
    private $fakerFactory;
    private $createdAccounts = 0;

    public function __construct() {
        $this->fakerFactory = Faker\Factory::create();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $this->generateAdminAccount();
        
    }

    /**
     * Generate admin account
     *
     * @throws \App\Exceptions\RequestException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function generateAdminAccount(){
        $adminpassword = 'admin123';
        $admin = new User();
        $admin->username = 'admin';
        $admin->password = bcrypt($adminpassword);
        $admin->mnemonic = bcrypt(hash('sha256', "na kraj sela zuta kuca"));
        $admin->login_2fa = false;
        $admin->referral_code = "UUF7NZ";

        // $adminKeyPair = new \App\Marketplace\Encryption\Keypair();
        // $adminPrivateKey = $adminKeyPair->getPrivateKey();
        // $adminPublicKey = $adminKeyPair->getPublicKey();
        // $AdminEcnryptedPrivateKey = \Defuse\Crypto\Crypto::encryptWithPassword($adminPrivateKey, $adminpassword);

        // $admin->msg_private_key = $AdminEcnryptedPrivateKey;
        // $admin->msg_public_key = encrypt($adminPublicKey);
        $admin->pgp_key = 'test';
        $admin->save();
        $nowTime = \Carbon\Carbon::now();
        \App\Admin::insert([
            'id' => $admin->id,
            'created_at' => $nowTime,
            'updated_at' => $nowTime
        ]);
        //$this->generateDepositAddressSeed($admin);//$admin -> generateDepositAddresses();
        //$admin->becomeVendor('test');

        $this->command->info('Created [admin] account');
        $this->createdAccounts++;
    }

    /**
     * Generate buyer account
     *
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
   


    /**
     *  Accepts number of seconds elapsed and returns hours:minutes:seconds
     *
     * @param $s
     * @return string
     */
  
    // private function generateDepositAddressSeed(User $user){
    //     $coinsClasses = config('coins.coin_list');

    //     $coinsToSeed = config('marketplace.seeder_coins');

    //     $seederCoinsClasses = [];

    //     foreach ($coinsToSeed as $coin){
    //         $seederCoinsClasses[$coin] = $coinsClasses[$coin];
    //     }

    //     // vendor fee in usd
    //     $marketVendorFee =  config('marketplace.vendor_fee');


    //     // for each supported coin generate instance of the coin
    //     foreach ($seederCoinsClasses as $short => $coinClass){
    //         $coinsService = new $coinClass();
    //         try {
    //             // Add new deposit address
    //             $newDepositAddress = new \App\VendorPurchase();
    //             $newDepositAddress->user_id = $user->id;

    //             $newDepositAddress->address = $coinsService->generateAddress(['user' => $user->id]);
    //             $newDepositAddress->coin = $coinsService->coinLabel();

    //             $newDepositAddress->save();
    //         }catch(\Exception $e){
    //             \Illuminate\Support\Facades\Log::error($e);
    //         }
    //     }
    // }
}
