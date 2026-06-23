@extends('layouts.app')

@section('title', 'กฎเกณฑ์พฤติกรรม')
@section('page-title', 'กฎเกณฑ์พฤติกรรม')

@section('content')
<div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
        <h2>กฎเกณฑ์การประเมินพฤติกรรม</h2>
        <p>กำหนดประเภทและคะแนนสำหรับแต่ละพฤติกรรม</p>
    </div>
    <a href="{{ route('discipline.behavior-rules.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่มกฎเกณฑ์
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ประเภท</th>
                    <th>หมวดหมู่</th>
                    <th>ชื่อกฎ</th>
                    <th style="text-align:center;">คะแนนที่เปลี่ยน</th>
                    <th style="text-align:right;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr>
                    <td>
                        <span class="badge {{ $rule->RuleType === 'ตัดคะแนน' ? 'badge-red' : 'badge-green' }}">
                            {{ $rule->RuleType === 'ตัดคะแนน' ? '▼ ตัดคะแนน' : '▲ เพิ่มคะแนน' }}
                        </span>
                    </td>
                    <td><span class="badge badge-gray">{{ $rule->Category }}</span></td>
                    <td>{{ $rule->RuleName }}</td>
                    <td style="text-align:center;">
                        <strong style="color: {{ $rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}; font-size:1rem;">
                            {{ $rule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($rule->ScoreModifier) }}
                        </strong>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('discipline.behavior-rules.edit', $rule->RuleID) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('discipline.behavior-rules.destroy', $rule->RuleID) }}"
                                  onsubmit="return confirm('ยืนยันการลบกฎเกณฑ์นี้?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">ยังไม่มีกฎเกณฑ์</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem; border-top:1px solid #ede8e0;">
        {{ $rules->links() }}
    </div>
</div>
@endsection