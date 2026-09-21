# -*- coding: utf-8 -*-
import os
import time
from playwright.sync_api import sync_playwright

BASE_URL = "http://127.0.0.1:8000"
IMG_DIR = os.path.abspath("docs/manual_images")
os.makedirs(IMG_DIR, exist_ok=True)

def login(page, username, password="password123"):
    print(f"Logging in as {username}...")
    page.goto(f"{BASE_URL}/logout", wait_until="networkidle")
    page.goto(f"{BASE_URL}/login", wait_until="networkidle")
    time.sleep(0.5)
    
    # Fill Username and Password (field names are capitalized in login.blade.php)
    page.fill('input#Username', username)
    page.fill('input#Password', password)
    page.click('button[type="submit"]')
    page.wait_for_load_state("networkidle")
    time.sleep(1)
    
    # Check if select-role page
    if "/select-role" in page.url:
        print("Encountered /select-role, selecting first role...")
        btn = page.query_selector('button[type="submit"]')
        if btn:
            btn.click()
            page.wait_for_load_state("networkidle")
            time.sleep(1)

def capture_page(page, url_path, filename, wait_seconds=1):
    full_url = f"{BASE_URL}{url_path}" if not url_path.startswith("http") else url_path
    print(f"Capturing: {url_path} -> {filename}")
    try:
        page.goto(full_url, wait_until="networkidle", timeout=15000)
    except Exception as e:
        print(f"Wait timeout on {full_url}, proceeding: {e}")
    time.sleep(wait_seconds)
    out_path = os.path.join(IMG_DIR, filename)
    page.screenshot(path=out_path, full_page=False)
    print(f"Saved: {out_path}")
    return out_path

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        # 1280x800 resolution with 1.5 device scale factor for crystal clear quality
        context = browser.new_context(viewport={"width": 1280, "height": 820}, device_scale_factor=1.5)
        page = context.new_page()

        # -------------------------------------------------------------
        # Fig 1: Login Page
        # -------------------------------------------------------------
        page.goto(f"{BASE_URL}/logout", wait_until="networkidle")
        capture_page(page, "/login", "fig_01.png", wait_seconds=1)

        # -------------------------------------------------------------
        # Admin Screens (Fig 2 - 12)
        # -------------------------------------------------------------
        login(page, "admin")
        capture_page(page, "/admin/dashboard", "fig_02.png", wait_seconds=1.5)
        capture_page(page, "/admin/users", "fig_03.png", wait_seconds=1)
        capture_page(page, "/admin/users/create", "fig_04.png", wait_seconds=1)
        capture_page(page, "/admin/students", "fig_05.png", wait_seconds=1)
        capture_page(page, "/admin/students/create", "fig_06.png", wait_seconds=1)
        
        # Valid student ID 6910101 for card and parents
        capture_page(page, "/admin/students/6910101/card", "fig_07.png", wait_seconds=1.5)
        capture_page(page, "/admin/students/import", "fig_08.png", wait_seconds=1)
        capture_page(page, "/admin/students/6910101/parents", "fig_09.png", wait_seconds=1)
        capture_page(page, "/admin/teachers", "fig_10.png", wait_seconds=1)
        capture_page(page, "/admin/permissions", "fig_11.png", wait_seconds=1)
        
        # Semester indicator on dashboard
        capture_page(page, "/admin/dashboard", "fig_12.png", wait_seconds=1)

        # -------------------------------------------------------------
        # Discipline Screens (Fig 13 - 20, 24)
        # -------------------------------------------------------------
        login(page, "discipline1")
        capture_page(page, "/discipline/dashboard", "fig_13.png", wait_seconds=1.5)
        capture_page(page, "/discipline/risk-students", "fig_14.png", wait_seconds=1)
        capture_page(page, "/discipline/behavior-rules", "fig_15.png", wait_seconds=1)
        capture_page(page, "/discipline/behavior-rules/create", "fig_16.png", wait_seconds=1)
        capture_page(page, "/discipline/behavior-records", "fig_17.png", wait_seconds=1)
        capture_page(page, "/discipline/appeals", "fig_18.png", wait_seconds=1)
        capture_page(page, "/discipline/informant-reports", "fig_19.png", wait_seconds=1)
        capture_page(page, "/discipline/behavior-report", "fig_20.png", wait_seconds=1)
        # Prayer scan requires discipline role
        capture_page(page, "/prayer/scan", "fig_24.png", wait_seconds=1.5)

        # -------------------------------------------------------------
        # Teacher Screens (Fig 21 - 23, 25)
        # -------------------------------------------------------------
        login(page, "teacher1")
        capture_page(page, "/teacher/dashboard", "fig_21.png", wait_seconds=1.5)
        capture_page(page, "/teacher/attendance", "fig_22.png", wait_seconds=1)
        capture_page(page, "/teacher/behavior-records/create", "fig_23.png", wait_seconds=1)
        capture_page(page, "/teacher/messages", "fig_25.png", wait_seconds=1)

        # -------------------------------------------------------------
        # Student Screens (Fig 26 - 29)
        # -------------------------------------------------------------
        login(page, "student1")
        capture_page(page, "/student/dashboard", "fig_26.png", wait_seconds=1.5)
        capture_page(page, "/student/appeals/create", "fig_27.png", wait_seconds=1)
        capture_page(page, "/student/prayer-checkin", "fig_28.png", wait_seconds=1.5)
        capture_page(page, "/student/informant-reports/create", "fig_29.png", wait_seconds=1)

        # -------------------------------------------------------------
        # Parent Screens (Fig 30 - 32)
        # -------------------------------------------------------------
        login(page, "parent1")
        capture_page(page, "/parent/dashboard", "fig_30.png", wait_seconds=1.5)
        capture_page(page, "/parent/behavior-records", "fig_31.png", wait_seconds=1)
        capture_page(page, "/parent/messages", "fig_32.png", wait_seconds=1)

        browser.close()
        print("\nAll 32 screenshots captured successfully!")

if __name__ == "__main__":
    run()
