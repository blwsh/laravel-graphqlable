<?php

namespace UniBen\LaravelGraphQLable\Models;

class GraphQLModelStub extends GraphQLModel
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
