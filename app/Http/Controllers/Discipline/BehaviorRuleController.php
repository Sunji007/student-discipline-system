<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BehaviorRuleController extends Controller
{
    public function index()
    {
        $rules = BehaviorRule::orderBy('RuleType')->orderBy('Category')->paginate(20);
        return view('discipline.behavior-rules.index', compact('rules'));
    }

    public function create()
    {
        return view('discipline.behavior-rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'RuleType'      => 'required|in:ตัดคะแนน,เพิ่มคะแนน',
            'Category'      => 'required|string|max:50',
            'RuleName'      => 'required|string|max:255',
            'ScoreModifier' => 'required|integer',
        ]);

        BehaviorRule::create(['RuleID' => Str::uuid(), ...$validated]);

        return redirect()->route('discipline.behavior-rules.index')
            ->with('success', 'เพิ่มกฎเกณฑ์เรียบร้อยแล้ว');
    }

    public function edit(BehaviorRule $behaviorRule)
    {
        return view('discipline.behavior-rules.edit', compact('behaviorRule'));
    }

    public function update(Request $request, BehaviorRule $behaviorRule)
    {
        $validated = $request->validate([
            'RuleType'      => 'required|in:ตัดคะแนน,เพิ่มคะแนน',
            'Category'      => 'required|string|max:50',
            'RuleName'      => 'required|string|max:255',
            'ScoreModifier' => 'required|integer',
        ]);

        $behaviorRule->update($validated);

        return redirect()->route('discipline.behavior-rules.index')
            ->with('success', 'แก้ไขกฎเกณฑ์เรียบร้อยแล้ว');
    }

    public function destroy(BehaviorRule $behaviorRule)
    {
        $behaviorRule->delete();
        return redirect()->route('discipline.behavior-rules.index')
            ->with('success', 'ลบกฎเกณฑ์เรียบร้อยแล้ว');
    }
}