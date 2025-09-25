<?php

namespace GFUnit\pim\sync;
use GFBiz\units\UnitController;

class KontainerCom extends UnitController
{
    private $bearer = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
    //private $bearer = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
    public function __construct()
    {
        parent::__construct(__FILE__);

    }


    public function createNewItem($postData)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/variations/3603/items');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = $this->bearer;
        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;

    }

    public function updateItem($kontainerID,$postData){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items/'.$kontainerID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = $this->bearer;
        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }



    public function getDataOnItemnr($itemNr){

        //230178
        //230179
        $ch = curl_init();
        $url = 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items?filter[product_no][eq]='.urlencode($itemNr).'&filter[product_type][eq]=Product';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;

    }

    public function getData($channels,$updatedAt,$size)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/'.$channels.'/items?page[size]='.$size.'&filter[updated_at][gt]='.$updatedAt.'%2000:00:11');
      //  curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/'.$channels.'/items?page[size]='.$size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
      //  $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    public function getDataSingle($channels,$kontainerID)
    {
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items/'.$kontainerID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    public function getAll(){
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
     ///   $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    public function getImgUrl($id){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/dam/files/'.$id.'/cdn');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"data\": {\n    \"type\": \"cdn\"\n  }\n}");

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }


}
