<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRule;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BehaviorRuleController extends Controller
{
    public function index(Request $request)
    {
        // Deduplicate legacy duplicate rules automatically by RuleName
        $rulesList = BehaviorRule::all();
        $grouped = $rulesList->groupBy(function($r) {
            return mb_strtolower(trim($r->RuleName));
        });

        foreach ($grouped as $group) {
            if ($group->count() > 1) {
                $original = $group->first();
                $duplicates = $group->slice(1);
                foreach ($duplicates as $dup) {
                    BehaviorRecord::where('RuleID', $dup->RuleID)->update(['RuleID' => $original->RuleID]);
                    $dup->delete();
                }
            }
        }

        $query = BehaviorRule::query();

        if ($request->filled('type') && in_array($request->type, ['เพิ่มคะแนน', 'ตัดคะแนน'])) {
            $query->where('RuleType', $request->type);
        }

        $rules = $query->orderBy('RuleType')->orderBy('Category')->paginate(20)->withQueryString();
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
            'ScoreModifier' => 'required|integer|min:1|max:100',
        ], [
            'RuleType.required'      => 'กรุณาเลือกประเภทคะแนน',
            'Category.required'      => 'กรุณาระบุหมวดหมู่',
            'RuleName.required'      => 'กรุณาระบุชื่อเกณฑ์ประเมินพฤติกรรม',
            'ScoreModifier.required' => 'กรุณาระบุจำนวนคะแนน',
        ]);

        $ruleName = trim($validated['RuleName']);

        // Strictly check duplicate by RuleName regardless of Category/Type/Score
        $exists = BehaviorRule::whereRaw('LOWER(TRIM(RuleName)) = ?', [mb_strtolower($ruleName)])->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'RuleName' => 'ชื่อเกณฑ์ประเมินพฤติกรรมนี้ ("' . $ruleName . '") มีอยู่ในระบบแล้ว ไม่สามารถเพิ่มชื่อซ้ำได้ (แม้ว่าจะเลือกหมวดหมู่ต่างกันก็ตาม)'
            ]);
        }

        BehaviorRule::create([
            'RuleID'        => Str::uuid(),
            'RuleType'      => $validated['RuleType'],
            'Category'      => $validated['Category'],
            'RuleName'      => $ruleName,
            'ScoreModifier' => $validated['ScoreModifier'],
        ]);

        return redirect()->route('discipline.behavior-rules.index')
            ->with('success', 'เพิ่มเกณฑ์ประเมินพฤติกรรมเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $behaviorRule = ($id instanceof BehaviorRule) ? $id : BehaviorRule::find($id);
        if ($behaviorRule) {
            return redirect()->route('discipline.behavior-rules.edit', $behaviorRule->RuleID);
        }
        return redirect()->route('discipline.behavior-rules.index');
    }

    public function edit($id)
    {
        $behaviorRule = ($id instanceof BehaviorRule) ? $id : BehaviorRule::find($id);
        if (!$behaviorRule) {
            return redirect()->route('discipline.behavior-rules.index')->with('error', 'ไม่พบเกณฑ์ประเมินพฤติกรรมที่ระบุ');
        }
        return view('discipline.behavior-rules.edit', compact('behaviorRule'));
    }

    public function update(Request $request, $id)
    {
        $behaviorRule = ($id instanceof BehaviorRule) ? $id : BehaviorRule::find($id);
        if (!$behaviorRule) {
            return redirect()->route('discipline.behavior-rules.index')->with('error', 'ไม่พบเกณฑ์ประเมินพฤติกรรมที่ระบุ');
        }

        $validated = $request->validate([
            'RuleType'      => 'required|in:ตัดคะแนน,เพิ่มคะแนน',
            'Category'      => 'required|string|max:50',
            'RuleName'      => 'required|string|max:255',
            'ScoreModifier' => 'required|integer|min:1|max:100',
        ], [
            'RuleType.required'      => 'กรุณาเลือกประเภทคะแนน',
            'Category.required'      => 'กรุณาระบุหมวดหมู่',
            'RuleName.required'      => 'กรุณาระบุชื่อเกณฑ์ประเมินพฤติกรรม',
            'ScoreModifier.required' => 'กรุณาระบุจำนวนคะแนน',
        ]);

        $ruleName = trim($validated['RuleName']);

        // Strictly check duplicate by RuleName excluding self regardless of Category/Type/Score
        $exists = BehaviorRule::whereRaw('LOWER(TRIM(RuleName)) = ?', [mb_strtolower($ruleName)])
            ->where('RuleID', '!=', $behaviorRule->RuleID)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'RuleName' => 'ชื่อเกณฑ์ประเมินพฤติกรรมนี้ ("' . $ruleName . '") มีอยู่ในระบบแล้ว ไม่สามารถแก้ไขให้ซ้ำได้ (แม้ว่าจะเลือกหมวดหมู่ต่างกันก็ตาม)'
            ]);
        }

        $behaviorRule->update([
            'RuleType'      => $validated['RuleType'],
            'Category'      => $validated['Category'],
            'RuleName'      => $ruleName,
            'ScoreModifier' => $validated['ScoreModifier'],
        ]);

        return redirect()->route('discipline.behavior-rules.index')
            ->with('success', 'แก้ไขเกณฑ์ประเมินพฤติกรรมเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $behaviorRule = ($id instanceof BehaviorRule) ? $id : BehaviorRule::find($id);
        if ($behaviorRule) {
            $behaviorRule->delete();
            return redirect()->route('discipline.behavior-rules.index')
                ->with('success', 'ลบเกณฑ์ประเมินพฤติกรรมเรียบร้อยแล้ว');
        }
        return redirect()->route('discipline.behavior-rules.index')
            ->with('info', 'เกณฑ์ประเมินพฤติกรรมนี้ถูกลบไปแล้ว');
    }
}