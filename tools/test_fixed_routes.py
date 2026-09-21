# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

BASE = "http://127.0.0.1:8000"

def test_fixed():
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

        # 1. Admin Fig 07 & Fig 09
        do_login("admin")
        r7 = page.goto(f"{BASE}/admin/students/6910101/card")
        print(f"FIG 07 (/admin/students/6910101/card): {r7.status} | Title: {page.title()}")
        
        r9 = page.goto(f"{BASE}/admin/students/6910101/parents")
        print(f"FIG 09 (/admin/students/6910101/parents): {r9.status} | Title: {page.title()}")

        # 2. Prayer Scan Fig 24 with discipline1
        do_login("discipline1")
        r24 = page.goto(f"{BASE}/prayer/scan")
        print(f"FIG 24 (/prayer/scan as discipline1): {r24.status} | Title: {page.title()}")

        # 3. Parent Behavior Records Fig 31 with parent1
        do_login("parent1")
        r31 = page.goto(f"{BASE}/parent/behavior-records")
        print(f"FIG 31 (/parent/behavior-records as parent1): {r31.status} | Title: {page.title()}")

        browser.close()

if __name__ == "__main__":
    test_fixed()
