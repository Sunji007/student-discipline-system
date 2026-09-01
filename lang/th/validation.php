<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    | Translation: Thai
    |
    */

    'accepted'             => 'กรุณายอมรับ :attribute',
    'accepted_if'          => 'กรุณายอมรับ :attribute เมื่อ :other คือ :value',
    'active_url'           => ':attribute ไม่ใช่ URL ที่ถูกต้อง',
    'after'                => ':attribute ต้องเป็นวันที่หลังจาก :date',
    'after_or_equal'       => ':attribute ต้องเป็นวันที่หลังจากหรือวันเดียวกับ :date',
    'alpha'                => ':attribute ต้องประกอบด้วยตัวอักษรเท่านั้น',
    'alpha_dash'           => ':attribute ต้องประกอบด้วยตัวอักษร ตัวเลข ขีดกลาง และขีดล่างเท่านั้น',
    'alpha_num'            => ':attribute ต้องประกอบด้วยตัวอักษรและตัวเลขเท่านั้น',
    'array'                => ':attribute ต้องเป็นอาร์เรย์',
    'ascii'                => ':attribute ต้องประกอบด้วยอักขระ ASCII ขนาด 1 ไบต์เท่านั้น',
    'before'               => ':attribute ต้องเป็นวันที่ก่อน :date',
    'before_or_equal'      => ':attribute ต้องเป็นวันที่ก่อนหรือวันเดียวกับ :date',
    'between'              => [
        'array'   => ':attribute ต้องมีระหว่าง :min ถึง :max รายการ',
        'file'    => ':attribute ต้องมีขนาดระหว่าง :min ถึง :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าระหว่าง :min ถึง :max',
        'string'  => ':attribute ต้องมีความยาวระหว่าง :min ถึง :max ตัวอักษร',
    ],
    'boolean'              => ':attribute ต้องเป็นจริงหรือเท็จเท่านั้น',
    'can'                  => ':attribute มีค่าที่ไม่ได้รับอนุญาต',
    'confirmed'            => 'การยืนยัน :attribute ไม่ตรงกัน',
    'contains'             => ':attribute ขาดฟิลด์ที่กำหนด',
    'current_password'     => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
    'date'                 => ':attribute ไม่ใช่วันที่ที่ถูกต้อง',
    'date_equals'          => ':attribute ต้องเป็นวันที่ที่เท่ากับ :date',
    'date_format'          => ':attribute ไม่ตรงกับรูปแบบ :format',
    'decimal'              => ':attribute ต้องมีทศนิยม :decimal ตำแหน่ง',
    'declined'             => ':attribute ต้องถูกปฏิเสธ',
    'declined_if'          => ':attribute ต้องถูกปฏิเสธเมื่อ :other คือ :value',
    'different'            => ':attribute และ :other ต้องต่างกัน',
    'digits'               => ':attribute ต้องเป็นตัวเลขจำนวน :digits หลัก',
    'digits_between'       => ':attribute ต้องมีจำนวนหลักระหว่าง :min ถึง :max หลัก',
    'dimensions'           => ':attribute มีขนาดรูปภาพที่ไม่ถูกต้อง',
    'distinct'             => ':attribute มีค่าซ้ำกัน',
    'doesnt_end_with'      => ':attribute ต้องไม่ลงท้ายด้วยค่าต่อไปนี้: :values',
    'doesnt_start_with'    => ':attribute ต้องไม่เริ่มต้นด้วยค่าต่อไปนี้: :values',
    'email'                => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'ends_with'            => ':attribute ต้องลงท้ายด้วยค่าต่อไปนี้: :values',
    'enum'                 => ':attribute ที่เลือกไม่ถูกต้อง',
    'exists'               => ':attribute ที่เลือกไม่ถูกต้อง',
    'extensions'           => ':attribute ต้องมีนามสกุลไฟล์ดังนี้: :values',
    'file'                 => ':attribute ต้องเป็นไฟล์ข้อมูล',
    'filled'               => ':attribute ต้องมีข้อมูล',
    'gt'                   => [
        'array'   => ':attribute ต้องมีรายการมากกว่า :value รายการ',
        'file'    => ':attribute ต้องมีขนาดใหญ่กว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่ามากกว่า :value',
        'string'  => ':attribute ต้องมีความยาวมากกว่า :value ตัวอักษร',
    ],
    'gte'                  => [
        'array'   => ':attribute ต้องมีรายการไม่น้อยกว่า :value รายการ',
        'file'    => ':attribute ต้องมีขนาดใหญ่กว่าหรือเท่ากับ :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่ามากกว่าหรือเท่ากับ :value',
        'string'  => ':attribute ต้องมีความยาวไม่น้อยกว่า :value ตัวอักษร',
    ],
    'hex_color'            => ':attribute ต้องเป็นรหัสสีฐานสิบหกที่ถูกต้อง',
    'image'                => ':attribute ต้องเป็นรูปภาพ',
    'in'                   => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array'             => ':attribute ไม่มีอยู่ใน :other',
    'integer'              => ':attribute ต้องเป็นจำนวนเต็ม',
    'ip'                   => ':attribute ต้องเป็น IP address ที่ถูกต้อง',
    'ipv4'                 => ':attribute ต้องเป็น IPv4 address ที่ถูกต้อง',
    'ipv6'                 => ':attribute ต้องเป็น IPv6 address ที่ถูกต้อง',
    'json'                 => ':attribute ต้องเป็น JSON string ที่ถูกต้อง',
    'list'                 => ':attribute ต้องเป็นลิสต์ข้อมูล',
    'lowercase'            => ':attribute ต้องเป็นตัวพิมพ์เล็กเท่านั้น',
    'lt'                   => [
        'array'   => ':attribute ต้องมีรายการน้อยกว่า :value รายการ',
        'file'    => ':attribute ต้องมีขนาดเล็กกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าน้อยกว่า :value',
        'string'  => ':attribute ต้องมีความยาวน้อยกว่า :value ตัวอักษร',
    ],
    'lte'                  => [
        'array'   => ':attribute ต้องมีรายการไม่เกิน :value รายการ',
        'file'    => ':attribute ต้องมีขนาดเล็กกว่าหรือเท่ากับ :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าน้อยกว่าหรือเท่ากับ :value',
        'string'  => ':attribute ต้องมีความยาวไม่เกิน :value ตัวอักษร',
    ],
    'mac_address'          => ':attribute ต้องเป็น MAC address ที่ถูกต้อง',
    'max'                  => [
        'array'   => ':attribute ต้องมีรายการไม่เกิน :max รายการ',
        'file'    => ':attribute ต้องมีขนาดไม่เกิน :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าไม่เกิน :max',
        'string'  => ':attribute ต้องมีความยาวไม่เกิน :max ตัวอักษร',
    ],
    'max_width'            => ':attribute ต้องมีความกว้างไม่เกิน :max พิกเซล',
    'max_height'           => ':attribute ต้องมีความสูงไม่เกิน :max พิกเซล',
    'mimes'                => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'mimetypes'            => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'min'                  => [
        'array'   => ':attribute ต้องมีรายการอย่างน้อย :min รายการ',
        'file'    => ':attribute ต้องมีขนาดอย่างน้อย :min กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string'  => ':attribute ต้องมีความยาวอย่างน้อย :min ตัวอักษร',
    ],
    'min_width'            => ':attribute ต้องมีความกว้างอย่างน้อย :min พิกเซล',
    'min_height'           => ':attribute ต้องมีความสูงอย่างน้อย :min พิกเซล',
    'missing'              => 'ข้อมูล :attribute ต้องไม่มีอยู่',
    'missing_if'           => 'ข้อมูล :attribute ต้องไม่มีอยู่เมื่อ :other คือ :value',
    'missing_unless'       => 'ข้อมูล :attribute ต้องไม่มีอยู่เว้นแต่ :other คือ :value',
    'missing_with'         => 'ข้อมูล :attribute ต้องไม่มีอยู่เมื่อมี :values',
    'missing_with_all'     => 'ข้อมูล :attribute ต้องไม่มีอยู่เมื่อมี :values ทั้งหมด',
    'multiple_of'          => ':attribute ต้องเป็นทวีคูณของ :value',
    'not_in'               => ':attribute ที่เลือกไม่ถูกต้อง',
    'not_regex'            => 'รูปแบบ :attribute ไม่ถูกต้อง',
    'numeric'              => ':attribute ต้องเป็นตัวเลข',
    'password'             => [
        'letters'       => ':attribute ต้องประกอบด้วยตัวอักษรอย่างน้อยหนึ่งตัว',
        'mixed'         => ':attribute ต้องประกอบด้วยตัวอักษรพิมพ์ใหญ่และตัวพิมพ์เล็กอย่างน้อยหนึ่งตัว',
        'numbers'       => ':attribute ต้องประกอบด้วยตัวเลขอย่างน้อยหนึ่งตัว',
        'symbols'       => ':attribute ต้องประกอบด้วยสัญลักษณ์อย่างน้อยหนึ่งตัว',
        'uncompromised' => ':attribute ที่กรอกปรากฏในข้อมูลรั่วไหล กรุณาเลือก :attribute ใหม่',
    ],
    'present'              => 'ช่องข้อมูล :attribute ต้องมีอยู่',
    'present_if'           => 'ช่องข้อมูล :attribute ต้องมีอยู่เมื่อ :other คือ :value',
    'present_unless'       => 'ช่องข้อมูล :attribute ต้องมีอยู่เว้นแต่ :other คือ :value',
    'present_with'         => 'ช่องข้อมูล :attribute ต้องมีอยู่เมื่อมี :values',
    'present_with_all'     => 'ช่องข้อมูล :attribute ต้องมีอยู่เมื่อมี :values ทั้งหมด',
    'prohibited'           => 'ช่องข้อมูล :attribute ถูกห้าม',
    'prohibited_if'        => 'ช่องข้อมูล :attribute ถูกห้ามเมื่อ :other คือ :value',
    'prohibited_unless'    => 'ช่องข้อมูล :attribute ถูกห้ามเว้นแต่ :other มีค่าอยู่ใน :values',
    'prohibits'            => 'ช่องข้อมูล :attribute ห้ามระบุข้อมูลใน :other',
    'regex'                => 'รูปแบบ :attribute ไม่ถูกต้อง',
    'required'             => 'กรุณากรอกข้อมูลในช่อง :attribute',
    'required_if'          => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อ :other คือ :value',
    'required_if_accepted' => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อยอมรับ :other',
    'required_if_declined' => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อปฏิเสธ :other',
    'required_unless'      => 'กรุณากรอกข้อมูลในช่อง :attribute เว้นแต่ :other คือ :values',
    'required_with'        => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อระบุ :values',
    'required_with_all'    => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อระบุ :values ทั้งหมด',
    'required_without'     => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อไม่ระบุ :values',
    'required_without_all' => 'กรุณากรอกข้อมูลในช่อง :attribute เมื่อไม่ระบุ :values ทั้งหมด',
    'same'                 => ':attribute และ :other ต้องตรงกัน',
    'size'                 => [
        'array'   => ':attribute ต้องมีจำนวน :size รายการ',
        'file'    => ':attribute ต้องมีขนาด :size กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าเท่ากับ :size',
        'string'  => ':attribute ต้องมีความยาว :size ตัวอักษร',
    ],
    'starts_with'          => ':attribute ต้องเริ่มต้นด้วยค่าต่อไปนี้: :values',
    'string'               => ':attribute ต้องเป็นข้อความ',
    'timezone'             => ':attribute ต้องเป็นเขตเวลาที่ถูกต้อง',
    'unique'               => ':attribute นี้ถูกใช้งานในระบบแล้ว',
    'uploaded'             => ':attribute อัปโหลดไม่สำเร็จ',
    'uppercase'            => ':attribute ต้องเป็นตัวพิมพ์ใหญ่เท่านั้น',
    'url'                  => 'รูปแบบ :attribute ไม่ถูกต้อง',
    'ulid'                 => ':attribute ต้องเป็น ULID ที่ถูกต้อง',
    'uuid'                 => ':attribute ต้องเป็น UUID ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    | Example:
    | 'email.required' => 'We need to know your email address!',
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'Username'     => 'รหัสประจำตัว/รหัสนักเรียน',
        'username'     => 'รหัสประจำตัว/รหัสนักเรียน',
        'Password'     => 'รหัสผ่าน',
        'password'     => 'รหัสผ่าน',
        'FirstName'    => 'ชื่อจริง (ภาษาไทย)',
        'first_name'   => 'ชื่อจริง (ภาษาไทย)',
        'LastName'     => 'นามสกุล (ภาษาไทย)',
        'last_name'    => 'นามสกุล (ภาษาไทย)',
        'FirstName_EN' => 'ชื่อจริง (ภาษาอังกฤษ)',
        'LastName_EN'  => 'นามสกุล (ภาษาอังกฤษ)',
        'CitizenID'    => 'รหัสบัตรประชาชน',
        'citizen_id'   => 'รหัสบัตรประชาชน',
        'Phone'        => 'เบอร์โทรศัพท์',
        'phone'        => 'เบอร์โทรศัพท์',
        'Email'        => 'อีเมล',
        'email'        => 'อีเมล',
        'Address'      => 'ที่อยู่',
        'address'      => 'ที่อยู่',
        'Role'         => 'บทบาท',
        'role'         => 'บทบาท',
        'Status'       => 'สถานะ',
        'status'       => 'สถานะ',
        'TeacherID'    => 'รหัสครู',
        'teacher_id'   => 'รหัสครู',
        'teacher i d'  => 'รหัสครู',
        'StudentID'    => 'รหัสนักเรียน',
        'student_id'   => 'รหัสนักเรียน',
        'student i d'  => 'รหัสนักเรียน',
        'ParentID'     => 'รหัสผู้ปกครอง',
        'parent_id'    => 'รหัสผู้ปกครอง',
        'parent i d'   => 'รหัสผู้ปกครอง',
    ],

];
