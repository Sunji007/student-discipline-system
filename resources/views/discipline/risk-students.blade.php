@extends('layouts.app')

@section('title', 'นักเรียนกลุ่มเสี่ยง')
@section('page-title', 'นักเรียนกลุ่มเสี่ยง')

@section('content')
<div class="page-header">
    <h2>รายชื่อนักเรียนกลุ่มเสี่ยง</h2>
    <p>นักเรียนที่มีคะแนนพฤติกรรมต่ำกว่า 80 คะแนน</p>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
    @forelse($students as $s)
    @php
        $scoreColor  = $s->BehaviorScore < 60 ? 'var(--red)' : 'var(--orange)';
        $borderColor = $s->RiskStatus === 'วิกฤต' ? 'var(--red)' : 'var(--orange)';
        $pct = $s->BehaviorScore;
    @endphp
    <div class="card" style="border-top:3px solid {{ $borderColor }};">
        <div style="padding:1.25rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.75rem;">
                <div>
                    <div style="font-weight:600; color:var(--navy);">{{ $s->FullName }}</div>
                    <div style="font-size:0.78rem; color:var(--text-muted);">
                        รหัส: {{ $s->StudentID }} &nbsp;|&nbsp; ห้อง: {{ $s->Classroom }}
                    </div>
                </div>
                <span class="badge {{ $s->RiskStatus === 'วิกฤต' ? 'badge-red' : 'badge-orange' }}">
                    {{ $s->RiskStatus }}
                </span>
            </div>

            {{-- Score Bar --}}
            <div style="margin-bottom:0.75rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                    <span style="font-size:0.75rem; color:var(--text-muted);">คะแนนพฤติกรรม</span>
                    <span style="font-size:1.1rem; font-weight:700; color:{{ $scoreColor }};">{{ $pct }}</span>
                </div>
                <div style="height:8px; background:#e8e3db; border-radius:4px; overflow:hidden;">
                    <div style="height:100%; width:{{ $pct }}%; background:{{ $scoreColor }}; border-radius:4px; transition:width 1s;"></div>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; font-size:0.78rem; color:var(--text-muted);">
                <div style="background:#faf8f4; padding:0.4rem 0.6rem; border-radius:2px;">
                    <i class="fas fa-clipboard-list" style="color:var(--gold); margin-right:0.3rem;"></i>
                    บันทึกทั้งหมด: {{ $s->behaviorRecords()->count() }}
                </div>
                <div style="background:#faf8f4; padding:0.4rem 0.6rem; border-radius:2px;">
                    <i class="fas fa-user-times" style="color:var(--red); margin-right:0.3rem;"></i>
                    ขาดเรียน: {{ $s->attendances()->where('Status', 'ขาด')->count() }}
                </div>
            </div>
        </div>

        <div style="padding:0.75rem 1.25rem; border-top:1px solid #ede8e0; display:flex; gap:0.5rem;">
            <a href="{{ route('discipline.behavior-records.create') }}?student={{ $s->StudentID }}"
               class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">
                <i class="fas fa-plus"></i> บันทึกพฤติกรรม
            </a>
            <a href="{{ route('discipline.messages.create') }}?receiver={{ $s->parent?->UserID }}"
               class="btn btn-outline btn-sm">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1;">
        <div style="text-align:center; padding:3rem; color:var(--text-muted);">
            <i class="fas fa-smile" style="font-size:2.5rem; color:var(--green); opacity:0.5; display:block; margin-bottom:1rem;"></i>
            <div style="font-size:1rem; font-weight:500;">ไม่มีนักเรียนกลุ่มเสี่ยง 🎉</div>
            <div style="font-size:0.85rem; margin-top:0.35rem;">นักเรียนทุกคนมีคะแนนพฤติกรรมในเกณฑ์ปกติ</div>
        </div>
    </div>
    @endforelse
</div>

@if($students->hasPages())
<div style="margin-top:1.25rem;">
    {{ $students->links() }}
</div>
@endif
@endsection