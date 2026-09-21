# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

BASE = "http://127.0.0.1:8000"

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        page = browser.new_page()

        def do_login(user):
            page.goto(f"{BASE}/logout")
            page.goto(f"{BASE}/login")
            page.fill("input#Username", user)
            page.fill("input#Password", "password123")
            with page.expect_navigation():
                page.click("button.btn-login")
            if "/select-role" in page.url:
                btn = page.query_selector("button[type='submit']")
                if btn:
                    with page.expect_navigation():
                        btn.click()

        # Test items: (fig_num, user, path)
        items = [
            (1, "guest", "/login"),
            (2, "admin", "/admin/dashboard"),
            (3, "admin", "/admin/users"),
            (4, "admin", "/admin/users/create"),
            (5, "admin", "/admin/students"),
            (6, "admin", "/admin/students/create"),
            (7, "admin", "/admin/students/6910101/card"),
            (8, "admin", "/admin/students/import"),
            (9, "admin", "/admin/students/6910101/parents"),
            (10, "admin", "/admin/teachers"),
            (11, "admin", "/admin/permissions"),
            (12, "admin", "/admin/dashboard"),
            (13, "discipline1", "/discipline/dashboard"),
            (14, "discipline1", "/discipline/risk-students"),
            (15, "discipline1", "/discipline/behavior-rules"),
            (16, "discipline1", "/discipline/behavior-rules/create"),
            (17, "discipline1", "/discipline/behavior-records"),
            (18, "discipline1", "/discipline/appeals"),
            (19, "discipline1", "/discipline/informant-reports"),
            (20, "discipline1", "/discipline/behavior-report"),
            (21, "teacher1", "/teacher/dashboard"),
            (22, "teacher1", "/teacher/attendance"),
            (23, "teacher1", "/teacher/behavior-records/create"),
            (24, "teacher1", "/prayer/scan"),
            (25, "teacher1", "/teacher/messages"),
            (26, "student1", "/student/dashboard"),
            (27, "student1", "/student/appeals/create"),
            (28, "student1", "/student/prayer-checkin"),
            (29, "student1", "/student/informant-reports/create"),
            (30, "parent1", "/parent/dashboard"),
            (31, "parent1", "/parent/behavior-records"),
            (32, "parent1", "/parent/messages"),
        ]

        current_user = None
        for fig, user, path in items:
            if user != current_user:
                if user != "guest":
                    do_login(user)
                current_user = user
                
            resp = page.goto(f"{BASE}{path}")
            status = resp.status if resp else "no response"
            title = page.title()
            content_snippet = page.content()[:300]
            is_404 = (status == 404) or ("404" in title) or ("Not Found" in title)
            print(f"FIG {fig:02d}: Status={status} | 404={is_404} | URL={page.url} | Title={title}")

        browser.close()

if __name__ == "__main__":
    run()
