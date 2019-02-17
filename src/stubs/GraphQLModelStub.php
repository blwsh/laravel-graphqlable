<?php

namespace UniBen\LaravelGraphQLable\Database\Factories;


use Illuminate\Database\Eloquent\Model;

class GraphQLModelStub extends Model
{
    protected $table = 'graphql_tests';

    protected $fillable = [
        'text',
        'time',
        'created_at',
        'update_at'
    ];

    protected $guarded = [
        'ipAddress'
    ];

    public function owner() {
        return $this->belongsTo('App\User');
    }

    public function user() {
        return $this->hasOne('App\User');
    }
}
