# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

BASE = "http://127.0.0.1:8000"

def check():
    with sync_playwright() as p:
        browser = p.chromium.launch(channel="msedge", headless=True)
        page = browser.new_page()
        
        users = ["admin", "discipline1", "teacher1", "student1", "parent1"]
        
        for u in users:
            print(f"\n--- LOGGING IN AS {u} ---")
            page.goto(f"{BASE}/logout", wait_until="load")
            page.goto(f"{BASE}/login", wait_until="load")
            page.fill("input#Username", u)
            page.fill("input#Password", "password123")
            page.click("button[type='submit']")
            page.wait_for_load_state("load")
            if "/select-role" in page.url:
                btn = page.query_selector("button[type='submit']")
                if btn: btn.click()
                page.wait_for_load_state("load")
            print(f"Logged in as {u}. Current URL: {page.url}")

        browser.close()

if __name__ == "__main__":
    check()
