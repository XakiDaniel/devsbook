<?php
namespace src\handlers;

use \src\models\Post;
use \src\models\User;
use \src\models\UserRelation;

class PostHandler {

    public static function addPost($idUser, $type, $body) {

        $body = trim($body);

        if(!empty($idUser) && !empty($body)) {

            Post::insert([
                'id_user' => $idUser,
                'type' => $type,
                //'created_at' => date('Y-m-d H:i:s') mexi na coluna do banco adicionando current time
                'body' => $body
            ])->execute();
        }
    }


    public static function getHomeFeed($idUser) {
        // 1. pegar lista de usuários que EU sigo.
        $userList = UserRelation::select()->where('user_from', $idUser)->get();
        $users = [];
        foreach($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }
        $users[] = $idUser;
        #print_r($users);
        // 2. pegar os post dessa galera ordenado pela data
        $postList = Post::select()->where('id_user', 'in', $users)->orderBy('created_at', 'desc')->get();
        echo '<pre>';
        print_r($postList);
        echo '</pre>';
        // 3. tranformar o resultado em objeto dos models
        $posts = [];
        foreach($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            
            // 4. preenhcer as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];


            //4.1 Preencher informações de Like
            //4.1 Preencher informações de comentarios

            $posts[] = $newPost;

        }
        // 5. retornar o resultado
        return $posts;
    }
}