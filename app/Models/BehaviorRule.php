<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BehaviorRule extends Model {
    protected $primaryKey = 'RuleID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['RuleID', 'RuleName', 'RuleType', 'ScoreModifier', 'Category', 'Description'];
}