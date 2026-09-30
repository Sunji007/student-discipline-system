# -*- coding: utf-8 -*-
import os
import time
from playwright.sync_api import sync_playwright

BASE_URL = "http://127.0.0.1:8000"
IMG_DIR = os.path.abspath("docs/manual_images")
os.makedirs(IMG_DIR, exist_ok=True)

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        context = browser.new_context(viewport={"width": 1280, "height": 820}, device_scale_factor=1.5)
        page = context.new_page()

        print("Navigating to login...")
        page.goto(f"{BASE_URL}/logout", wait_until="networkidle")
        page.goto(f"{BASE_URL}/login", wait_until="networkidle")
        time.sleep(0.5)

        print("Submitting login form for parent1...")
        page.fill('input#Username', 'parent1')
        page.fill('input#Password', 'password123')
        page.click('button[type="submit"]')
        
        # Wait until page navigates away from /login
        try:
            page.wait_for_url(lambda u: "/login" not in u, timeout=10000)
        except Exception as e:
            print("Wait for URL timeout:", e)

        time.sleep(1)
        print(f"Current URL after login: {page.url}")

        if "/select-role" in page.url:
            print("Selecting role...")
            btn = page.query_selector('button[type="submit"]')
            if btn:
                btn.click()
                page.wait_for_load_state("networkidle")
                time.sleep(1)

        print(f"Navigating to parent dashboard: {BASE_URL}/parent/dashboard")
        page.goto(f"{BASE_URL}/parent/dashboard", wait_until="networkidle")
        time.sleep(2)

        out_path = os.path.join(IMG_DIR, "fig_30.png")
        page.screenshot(path=out_path, full_page=False)
        print(f"Saved: {out_path}")

        # Let's also check what element is rendered on the page
        switcher = page.query_selector('text=สลับดูบุตรหลาน')
        print("Found 'สลับดูบุตรหลาน' on page:", switcher is not None)
        
        topbar_switcher = page.query_selector('form#student-switch-form')
        print("Found 'form#student-switch-form' on topbar:", topbar_switcher is not None)

        browser.close()

if __name__ == "__main__":
    run()
