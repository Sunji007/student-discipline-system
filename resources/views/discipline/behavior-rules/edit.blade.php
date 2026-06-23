@extends('layouts.app')

@section('title', 'แก้ไขกฎเกณฑ์')
@section('page-title', 'แก้ไขกฎเกณฑ์พฤติกรรม')

@section('content')
<div style="max-width:580px;">
    <div class="card">
        <div class="card-header-bar">
            <h3>แก้ไขกฎเกณฑ์</h3>
            <a href="{{ route('discipline.behavior-rules.index') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
        <div class="card-body-pad">
            <form method="POST" action="{{ route('discipline.behavior-rules.update', $behaviorRule->RuleID) }}">
                @csrf @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">ประเภท <span style="color:var(--red)">*</span></label>
                        <select name="RuleType" class="form-control">
                            <option value="ตัดคะแนน" {{ old('RuleType', $behaviorRule->RuleType) === 'ตัดคะแนน' ? 'selected' : '' }}>▼ ตัดคะแนน</option>
                            <option value="เพิ่มคะแนน" {{ old('RuleType', $behaviorRule->RuleType) === 'เพิ่มคะแนน' ? 'selected' : '' }}>▲ เพิ่มคะแนน</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">หมวดหมู่ <span style="color:var(--red)">*</span></label>
                        <input type="text" name="Category" class="form-control"
                               value="{{ old('Category', $behaviorRule->Category) }}" list="category-list">
                        <datalist id="category-list">
                            @foreach(['การแต่งกาย','การเรียน','ความประพฤติ','การเข้าแถว','กิจกรรม','ความดี'] as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ชื่อกฎเกณฑ์ <span style="color:var(--red)">*</span></label>
                    <input type="text" name="RuleName" class="form-control"
                           value="{{ old('RuleName', $behaviorRule->RuleName) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">คะแนนที่เปลี่ยนแปลง <span style="color:var(--red)">*</span></label>
                    <input type="number" name="ScoreModifier" class="form-control"
                           value="{{ old('ScoreModifier', abs($behaviorRule->ScoreModifier)) }}" min="1" max="100" required>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.3rem;">
                        ปัจจุบัน: <strong style="color:{{ $behaviorRule->RuleType === 'ตัดคะแนน' ? 'var(--red)' : 'var(--green)' }}">
                            {{ $behaviorRule->RuleType === 'ตัดคะแนน' ? '-' : '+' }}{{ abs($behaviorRule->ScoreModifier) }}
                        </strong>
                    </div>
                </div>

                <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('discipline.behavior-rules.index') }}" class="btn btn-outline">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection