# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

BASE = "http://127.0.0.1:8000"

routes = [
    ("guest", "/login", 1),
    ("admin", "/admin/dashboard", 2),
    ("admin", "/admin/users", 3),
    ("admin", "/admin/users/create", 4),
    ("admin", "/admin/students", 5),
    ("admin", "/admin/students/create", 6),
    ("admin", "/admin/students/1/card", 7),
    ("admin", "/admin/students/import", 8),
    ("admin", "/admin/students/1/parents", 9),
    ("admin", "/admin/teachers", 10),
    ("admin", "/admin/permissions", 11),
    ("admin", "/admin/semesters", 12),
    ("discipline1", "/discipline/dashboard", 13),
    ("discipline1", "/discipline/risk-students", 14),
    ("discipline1", "/discipline/behavior-rules", 15),
    ("discipline1", "/discipline/behavior-rules/create", 16),
    ("discipline1", "/discipline/behavior-records", 17),
    ("discipline1", "/discipline/appeals", 18),
    ("discipline1", "/discipline/informant-reports", 19),
    ("discipline1", "/discipline/behavior-report", 20),
    ("teacher1", "/teacher/dashboard", 21),
    ("teacher1", "/teacher/attendance", 22),
    ("teacher1", "/teacher/behavior-records/create", 23),
    ("teacher1", "/prayer/scan", 24),
    ("teacher1", "/teacher/messages", 25),
    ("student1", "/student/dashboard", 26),
    ("student1", "/student/appeals/create", 27),
    ("student1", "/student/prayer-checkin", 28),
    ("student1", "/student/informant-reports/create", 29),
    ("parent1", "/parent/dashboard", 30),
    ("parent1", "/parent/behavior-records", 31),
    ("parent1", "/parent/messages", 32)
]

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        page = browser.new_page()
        
        curr_user = None
        results = []
        for user, url, fig in routes:
            if user != curr_user:
                page.goto(f"{BASE}/logout")
                if user != "guest":
                    page.goto(f"{BASE}/login")
                    page.fill("input#Username", user)
                    page.fill("input#Password", "password123")
                    page.click("button[type='submit']")
                    page.wait_for_load_state("domcontentloaded")
                    if "/select-role" in page.url:
                        btn = page.query_selector("button[type='submit']")
                        if btn:
                            btn.click()
                            page.wait_for_load_state("domcontentloaded")
                curr_user = user
                
            resp = page.goto(f"{BASE}{url}")
            status = resp.status if resp else "None"
            title = page.title()
            is_404 = (status == 404) or ("404" in title) or ("Not Found" in title)
            res = f"fig_{fig:02d}: [{status}] {url} -> {'>>> 404 ERROR <<<' if is_404 else 'OK'} (Title: {title})"
            print(res)
            results.append((fig, is_404, res))
            
        browser.close()
        
        print("\n=== SUMMARY OF 404 ERRORS ===")
        errors = [r for r in results if r[1]]
        if not errors:
            print("No 404 errors found!")
        else:
            for fig, _, text in errors:
                print(text)

if __name__ == "__main__":
    run()
