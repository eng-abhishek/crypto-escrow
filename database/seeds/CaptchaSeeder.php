<?php

use Illuminate\Database\Seeder;
use App\Captcha;

class CaptchaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('captchas')->insert([
                [
                'title' => 'BABY',
                'slug' => 'baby',
                'alt' => 'baby01',
                'image' => 'Frontend/assets/img/captcha/baby01.jpg',
               ],
               [
                'title' => 'BABY',
                'slug' => 'baby',
                'alt' => 'baby02',
                'image' => 'Frontend/assets/img/captcha/baby02.jpg',
               ],
               [
                'title' => 'BABY',
                'slug' => 'baby',
                'alt' => 'baby03',
                'image' => 'Frontend/assets/img/captcha/baby03.jpg',
               ],
               [
                'title' => 'BABY',
                'slug' => 'baby',
                'alt' => 'baby04',
                'image' => 'Frontend/assets/img/captcha/baby04.jpg',
               ],
               [
                'title' => 'BABY',
                'slug' => 'baby',
                'alt' => 'baby05',
                'image' => 'Frontend/assets/img/captcha/baby05.png',
               ],

            /*------------------------- Bag -------------------------------------*/

               [
                'title' => 'bag',
                'slug' => 'bag',
                'alt' => 'bag01',
                'image' => 'Frontend/assets/img/captcha/bag01.png',
               ],
               [
                'title' => 'bag',
                'slug' => 'bag',
                'alt' => 'bag02',
                'image' => 'Frontend/assets/img/captcha/bag02.png',
               ],
               [
                'title' => 'bag',
                'slug' => 'bag',
                'alt' => 'bag03',
                'image' => 'Frontend/assets/img/captcha/bag03.png',
               ],
               [
                'title' => 'bag',
                'slug' => 'bag',
                'alt' => 'bag04',
                'image' => 'Frontend/assets/img/captcha/bag04.jpg',
               ],
               [
                'title' => 'bag',
                'slug' => 'bag',
                'alt' => 'bag04',
                'image' => 'Frontend/assets/img/captcha/bag05.png',
               ],

            /*------------------------- Bat -------------------------------------*/

               [
                'title' => 'bat',
                'slug' => 'bat',
                'alt' => 'bat01',
                'image' => 'Frontend/assets/img/captcha/bat01.png',
               ],
               [
                'title' => 'bat',
                'slug' => 'bat',
                'alt' => 'bat02',
                'image' => 'Frontend/assets/img/captcha/bat02.png',
               ],
               [
                'title' => 'bat',
                'slug' => 'bat',
                'alt' => 'bat03',
                'image' => 'Frontend/assets/img/captcha/bat03.png',
               ],

            /*------------------------- Cake -------------------------------------*/

               [
                'title' => 'Cake',
                'slug' => 'cake',
                'alt' => 'cake01',
                'image' => 'Frontend/assets/img/captcha/cake01.png',
               ],
               [
                'title' => 'Cake',
                'slug' => 'cake',
                'alt' => 'cake02',
                'image' => 'Frontend/assets/img/captcha/cake02.png',
               ],
               [
                'title' => 'Cake',
                'slug' => 'cake',
                'alt' => 'cake03',
                'image' => 'Frontend/assets/img/captcha/cake03.png',
               ],

            /*------------------------- Cat -------------------------------------*/

                [
                'title' => 'Cat',
                'slug' => 'cat',
                'alt' => 'cat01',
                'image' => 'Frontend/assets/img/captcha/cat01.jpg',
               ],
               [
                'title' => 'Cat',
                'slug' => 'cat',
                'alt' => 'cat02',
                'image' => 'Frontend/assets/img/captcha/cat02.png',
               ],
               [
                'title' => 'Cat',
                'slug' => 'cat',
                'alt' => 'cat03',
                'image' => 'Frontend/assets/img/captcha/cat03.jpg',
               ],


            /*------------------------- flowers -------------------------------------*/

                [
                'title' => 'Flowers',
                'slug' => 'flowers',
                'alt' => 'flowers01',
                'image' => 'Frontend/assets/img/captcha/flowers01.png',
               ],
               [
                'title' => 'Flowers',
                'slug' => 'flowers',
                'alt' => 'flowers02',
                'image' => 'Frontend/assets/img/captcha/flowers02.png',
               ],
               [
                'title' => 'Flowers',
                'slug' => 'flowers',
                'alt' => 'flowers03',
                'image' => 'Frontend/assets/img/captcha/flowers03.jpg',
               ],


            /*------------------------- tree -------------------------------------*/

                [
                'title' => 'Tree',
                'slug' => 'tree',
                'alt' => 'tree01',
                'image' => 'Frontend/assets/img/captcha/tree01.png',
               ],
               [
                'title' => 'Tree',
                'slug' => 'tree',
                'alt' => 'tree02',
                'image' => 'Frontend/assets/img/captcha/tree02.jpg',
               ],
               [
                'title' => 'Tree',
                'slug' => 'tree',
                'alt' => 'tree03',
                'image' => 'Frontend/assets/img/captcha/tree03.jpg',
               ],


            /*------------------------- mount -------------------------------------*/

                [
                'title' => 'Mount',
                'slug' => 'mount',
                'alt' => 'mount01',
                'image' => 'Frontend/assets/img/captcha/mount01.png',
               ],
               [
                'title' => 'Mount',
                'slug' => 'mount',
                'alt' => 'mount02',
                'image' => 'Frontend/assets/img/captcha/mount02.png',
               ],
               [
                'title' => 'Mount',
                'slug' => 'mount',
                'alt' => 'mount03',
                'image' => 'Frontend/assets/img/captcha/mount03.jpg',
               ],

         ]);
    }
}
