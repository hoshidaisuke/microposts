<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * このユーザが所有する投稿
     */
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }

    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers', 'favorites']);
    }
    
    /**
     * このユーザがフォロー中のユーザ 
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    /**
     * このユーザをフォロー中のユーザ
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    /**
     * $userIdで指定されたユーザをフォローする
     */
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            // すでにフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    /**
     * $userIdで指定されたユーザをアンフォローする
     */
    public function unfollow($userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォロー
            return false;
        }
    }
    
    /**
     * 指定された$userIdのユーザをこのユーザがフォロー中であるか調べる。
     * フォロー中ならtrueを返す
     */
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }

    /**
     * このユーザとフォロー中ユーザの投稿に絞り込む
    */
    public function feed_microposts()
    {
        // このユーザがフォロー中のユーザのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザが所有する投稿に絞り込む
        return Micropost::whereIn('user_id', $userIds);
    }

    /**
    * $micropost_idで指定されたmicropostをお気に入り登録する。
    *
    * @param int $userId
    * @return bool
    */
    public function favorite($micropostId)
    {
        // すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            return false;
        } else {
            // お気に入り登録していなければお気に入り登録をする
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
    
    /**
    * $micropost_idで指定されたmicropostをお気に入り登録を外す。
    *
    * @param int $userId
    * @return bool
    */
    public function unfavorite($micropostId)
    {
        // すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            // すでにフォローしていればフォローを外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            return false;
        }
    }


    /**
    * このユーザがお気に入り登録してるmicropostの一覧
    */
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }


    /**
    * すでにお気に入り登録済みかどうか
    *
    * @param int $userId
    * @return bool
    */
    public function is_favorite($micropostId)
    {
        // ログインユーザのfavoritesの中に 同じ$micropostIdが存在するか
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }
    
}
