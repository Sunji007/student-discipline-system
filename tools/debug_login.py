# -*- coding: utf-8 -*-
from playwright.sync_api import sync_playwright

with sync_playwright() as p:
    browser = p.chromium.launch(channel="msedge", headless=True)
    page = browser.new_page()
    page.goto("http://127.0.0.1:8000/login")
    page.fill("input#Username", "admin")
    page.fill("input#Password", "password123")
    with page.expect_navigation():
        page.click("button.btn-login")
    print("Navigated to:", page.url)
    page.screenshot(path="docs/manual_images/debug_login.png")
    print("Page title:", page.title())
    err = page.query_selector(".alert-danger")
    if err:
        print("Alert danger:", err.inner_text())
    browser.close()
