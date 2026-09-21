# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

BASE_URL = 'http://127.0.0.1:8000'

def test_routes():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel='msedge', headless=True)
        page = browser.new_page()
        
        def login(user):
            page.goto(f'{BASE_URL}/logout', wait_until='load')
            page.goto(f'{BASE_URL}/login', wait_until='load')
            page.fill('input#Username', user)
            page.fill('input#Password', 'password123')
            page.click('button[type="submit"]')
            page.wait_for_load_state('load')
            if '/select-role' in page.url:
                btn = page.query_selector('button[type="submit"]')
                if btn: btn.click()
                page.wait_for_load_state('load')
                
        routes = [
            ('guest', '/login', 'fig_01'),
            ('admin', '/admin/dashboard', 'fig_02'),
            ('admin', '/admin/users', 'fig_03'),
            ('admin', '/admin/users/create', 'fig_04'),
            ('admin', '/admin/students', 'fig_05'),
            ('admin', '/admin/students/create', 'fig_06'),
            ('admin', '/admin/students/1/card', 'fig_07'),
            ('admin', '/admin/students/import', 'fig_08'),
            ('admin', '/admin/students/1/parents', 'fig_09'),
            ('admin', '/admin/teachers', 'fig_10'),
            ('admin', '/admin/permissions', 'fig_11'),
            ('admin', '/admin/semesters', 'fig_12'),
            ('discipline1', '/discipline/dashboard', 'fig_13'),
            ('discipline1', '/discipline/risk-students', 'fig_14'),
            ('discipline1', '/discipline/behavior-rules', 'fig_15'),
            ('discipline1', '/discipline/behavior-rules/create', 'fig_16'),
            ('discipline1', '/discipline/behavior-records', 'fig_17'),
            ('discipline1', '/discipline/appeals', 'fig_18'),
            ('discipline1', '/discipline/informant-reports', 'fig_19'),
            ('discipline1', '/discipline/behavior-report', 'fig_20'),
            ('teacher1', '/teacher/dashboard', 'fig_21'),
            ('teacher1', '/teacher/attendance', 'fig_22'),
            ('teacher1', '/teacher/behavior-records/create', 'fig_23'),
            ('teacher1', '/prayer/scan', 'fig_24'),
            ('teacher1', '/teacher/messages', 'fig_25'),
            ('student1', '/student/dashboard', 'fig_26'),
            ('student1', '/student/appeals/create', 'fig_27'),
            ('student1', '/student/prayer-checkin', 'fig_28'),
            ('student1', '/student/informant-reports/create', 'fig_29'),
            ('parent1', '/parent/dashboard', 'fig_30'),
            ('parent1', '/parent/behavior-records', 'fig_31'),
            ('parent1', '/parent/messages', 'fig_32')
        ]
        
        current_user = None
        for role, path, fig in routes:
            if role != current_user:
                if role != 'guest':
                    login(role)
                current_user = role
            resp = page.goto(f'{BASE_URL}{path}', wait_until='load')
            status = resp.status if resp else 'None'
            title = page.title()
            content = page.content()
            is_404 = status == 404 or '404' in title or 'Page Not Found' in content or 'Not Found' in title
            flag = '>>> 404 ERROR <<<' if is_404 else 'OK'
            print(f'{fig} [{status}] ({role}) {path} -> {flag} | URL: {page.url}')
            
        browser.close()

if __name__ == '__main__':
    test_routes()
