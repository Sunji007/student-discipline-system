@extends('layouts.app')

@section('title', 'เกณฑ์ประเมินพฤติกรรม')
@section('page-title', 'เกณฑ์ประเมินพฤติกรรม')

@section('content')
<div class="page-header">
    <h2>เกณฑ์ประเมินพฤติกรรม</h2>
    <p>กำหนดประเภทและคะแนนสำหรับแต่ละพฤติกรรม</p>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:1rem;">
    <!-- Filter Tabs -->
    <div class="filter-tab-bar">
        <a href="{{ route('discipline.behavior-rules.index') }}" 
           class="filter-tab-item {{ !request('type') ? 'active' : '' }}">
           <i class="fas fa-list"></i> รายการทั้งหมด
        </a>
        <a href="{{ route('discipline.behavior-rules.index', ['type' => 'เพิ่มคะแนน']) }}" 
           class="filter-tab-item active-success {{ request('type') === 'เพิ่มคะแนน' ? 'active' : '' }}">
           <i class="fas fa-plus-circle"></i> เพิ่มคะแนน
        </a>
        <a href="{{ route('discipline.behavior-rules.index', ['type' => 'ตัดคะแนน']) }}" 
           class="filter-tab-item active-danger {{ request('type') === 'ตัดคะแนน' ? 'active' : '' }}">
           <i class="fas fa-minus-circle"></i> ตัดคะแนน
        </a>
    </div>
    
    <!-- Add Button -->
    <a href="{{ route('discipline.behavior-rules.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> เพิ่ม
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
                    <td>
                        @php
                            $catColor = match($rule->Category) {
                                'การแต่งกายและทรงผม'           => ['bg' => '#f3e8ff', 'color' => '#6d28d9'],
                                'ความประพฤติและกริยามารยาท'   => ['bg' => '#fef3c7', 'color' => '#b45309'],
                                'สารเสพติดและของต้องห้าม'     => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                                'การใช้เครื่องมือสื่อสาร'       => ['bg' => '#e0f2fe', 'color' => '#0369a1'],
                                'การเข้าเรียนและระเบียบสถานศึกษา' => ['bg' => '#d1fae5', 'color' => '#047857'],
                                'ความดีและจิตอาสา'            => ['bg' => '#dcfce7', 'color' => '#15803d'],
                                'กิจกรรมและสร้างชื่อเสียง',
                                'กิจกรรมและผลงาน'           => ['bg' => '#e0f2fe', 'color' => '#0284c7'],
                                'ความประพฤติดีเด่นและวินัย',
                                'ความประพฤติดีเด่น'           => ['bg' => '#ede9fe', 'color' => '#7e22ce'],
                                'คุณธรรมและศาสนกิจ'           => ['bg' => '#ccfbf1', 'color' => '#0f766e'],
                                'ความเป็นผู้นำและการมีส่วนร่วม'  => ['bg' => '#ffedd5', 'color' => '#c2410c'],
                                'วิชาการและความขยันหมั่นเพียร'  => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
                                default                       => ['bg' => '#f1f5f9', 'color' => '#475569'],
                            };
                        @endphp
                        <span class="badge" style="background:{{ $catColor['bg'] }}; color:{{ $catColor['color'] }}; font-weight:600; padding:0.25rem 0.6rem;">
                            {{ $rule->Category }}
                        </span>
                    </td>
                    <td>{{ $rule->RuleName }}</td>
                    <td style="text-align:center;">
                        <strong style="color: {{ $rule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}; font-size:1rem;">
                            {{ abs($rule->ScoreModifier) }}
                        </strong>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex; gap:0.35rem; justify-content:flex-end;">
                            <a href="{{ route('discipline.behavior-rules.edit', $rule->RuleID) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('discipline.behavior-rules.destroy', $rule->RuleID) }}"
                                  data-confirm="ยืนยันการลบกฎเกณฑ์นี้?">
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