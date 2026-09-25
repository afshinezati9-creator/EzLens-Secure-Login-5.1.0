@echo off
setlocal
chcp 65001 >nul

set "BASE=%~dp0"
set "DIR=%BASE%assets\icons\modern"

if not exist "%DIR%" mkdir "%DIR%"

echo.
echo   EzLens Modern Icons Generator
echo   Target: %DIR%
echo.

REM ==========================================================
REM home.svg - Dashboard / Overview
REM ==========================================================
> "%DIR%\home.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/^>
echo   ^<polyline points="9 22 9 12 15 12 15 22"/^>
echo ^</svg^>
)
echo   [OK] home.svg

REM ==========================================================
REM package.svg - Orders
REM ==========================================================
> "%DIR%\package.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/^>
echo   ^<polyline points="3.27 6.96 12 12.01 20.73 6.96"/^>
echo   ^<line x1="12" y1="22.08" x2="12" y2="12"/^>
echo ^</svg^>
)
echo   [OK] package.svg

REM ==========================================================
REM eye.svg - Patient File / Prescriptions
REM ==========================================================
> "%DIR%\eye.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/^>
echo   ^<circle cx="12" cy="12" r="3"/^>
echo ^</svg^>
)
echo   [OK] eye.svg

REM ==========================================================
REM heart.svg - Wishlist / Favorites
REM ==========================================================
> "%DIR%\heart.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/^>
echo ^</svg^>
)
echo   [OK] heart.svg

REM ==========================================================
REM map-pin.svg - Addresses
REM ==========================================================
> "%DIR%\map-pin.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/^>
echo   ^<circle cx="12" cy="10" r="3"/^>
echo ^</svg^>
)
echo   [OK] map-pin.svg

REM ==========================================================
REM star.svg - Reviews / Ratings
REM ==========================================================
> "%DIR%\star.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/^>
echo ^</svg^>
)
echo   [OK] star.svg

REM ==========================================================
REM percent.svg - Discounts
REM ==========================================================
> "%DIR%\percent.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<line x1="19" y1="5" x2="5" y2="19"/^>
echo   ^<circle cx="6.5" cy="6.5" r="2.5"/^>
echo   ^<circle cx="17.5" cy="17.5" r="2.5"/^>
echo ^</svg^>
)
echo   [OK] percent.svg

REM ==========================================================
REM gift.svg - Gift Cards
REM ==========================================================
> "%DIR%\gift.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<polyline points="20 12 20 22 4 22 4 12"/^>
echo   ^<rect x="2" y="7" width="20" height="5"/^>
echo   ^<line x1="12" y1="22" x2="12" y2="7"/^>
echo   ^<path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/^>
echo   ^<path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/^>
echo ^</svg^>
)
echo   [OK] gift.svg

REM ==========================================================
REM wallet.svg - Wallet
REM ==========================================================
> "%DIR%\wallet.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/^>
echo   ^<path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/^>
echo   ^<path d="M18 12a2 2 0 0 0 0 4h4v-4z"/^>
echo ^</svg^>
)
echo   [OK] wallet.svg

REM ==========================================================
REM headset.svg - Support
REM ==========================================================
> "%DIR%\headset.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M3 18v-6a9 9 0 0 1 18 0v6"/^>
echo   ^<path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/^>
echo   ^<path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/^>
echo ^</svg^>
)
echo   [OK] headset.svg

REM ==========================================================
REM user.svg - Account / Avatar
REM ==========================================================
> "%DIR%\user.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/^>
echo   ^<circle cx="12" cy="7" r="4"/^>
echo ^</svg^>
)
echo   [OK] user.svg

REM ==========================================================
REM shield.svg - Security
REM ==========================================================
> "%DIR%\shield.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/^>
echo ^</svg^>
)
echo   [OK] shield.svg

REM ==========================================================
REM users.svg - Refer Friends
REM ==========================================================
> "%DIR%\users.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/^>
echo   ^<circle cx="9" cy="7" r="4"/^>
echo   ^<path d="M23 21v-2a4 4 0 0 0-3-3.87"/^>
echo   ^<path d="M16 3.13a4 4 0 0 1 0 7.75"/^>
echo ^</svg^>
)
echo   [OK] users.svg

REM ==========================================================
REM log-out.svg - Logout
REM ==========================================================
> "%DIR%\log-out.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/^>
echo   ^<polyline points="16 17 21 12 16 7"/^>
echo   ^<line x1="21" y1="12" x2="9" y2="12"/^>
echo ^</svg^>
)
echo   [OK] log-out.svg

REM ==========================================================
REM check-circle.svg - Delivered
REM ==========================================================
> "%DIR%\check-circle.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/^>
echo   ^<polyline points="22 4 12 14.01 9 11.01"/^>
echo ^</svg^>
)
echo   [OK] check-circle.svg

REM ==========================================================
REM x-circle.svg - Cancelled
REM ==========================================================
> "%DIR%\x-circle.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<circle cx="12" cy="12" r="10"/^>
echo   ^<line x1="15" y1="9" x2="9" y2="15"/^>
echo   ^<line x1="9" y1="9" x2="15" y2="15"/^>
echo ^</svg^>
)
echo   [OK] x-circle.svg

REM ==========================================================
REM refresh-cw.svg - Returned
REM ==========================================================
> "%DIR%\refresh-cw.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<polyline points="23 4 23 10 17 10"/^>
echo   ^<polyline points="1 20 1 14 7 14"/^>
echo   ^<path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/^>
echo   ^<path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/^>
echo ^</svg^>
)
echo   [OK] refresh-cw.svg

REM ==========================================================
REM shopping-cart.svg - Cart
REM ==========================================================
> "%DIR%\shopping-cart.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<circle cx="9" cy="21" r="1"/^>
echo   ^<circle cx="20" cy="21" r="1"/^>
echo   ^<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/^>
echo ^</svg^>
)
echo   [OK] shopping-cart.svg

REM ==========================================================
REM shop.svg - Store
REM ==========================================================
> "%DIR%\shop.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<path d="M3 9l1.5-6h15L21 9"/^>
echo   ^<path d="M3 9v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9"/^>
echo   ^<line x1="3" y1="9" x2="21" y2="9"/^>
echo   ^<path d="M8 13a2 2 0 0 0 4 0"/^>
echo   ^<path d="M12 13a2 2 0 0 0 4 0"/^>
echo ^</svg^>
)
echo   [OK] shop.svg

REM ==========================================================
REM layout.svg - Coming Soon Sections
REM ==========================================================
> "%DIR%\layout.svg" (
echo ^<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"^>
echo   ^<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/^>
echo   ^<line x1="3" y1="9" x2="21" y2="9"/^>
echo   ^<line x1="9" y1="21" x2="9" y2="9"/^>
echo ^</svg^>
)
echo   [OK] layout.svg

echo.
echo   All 20 icons generated successfully.
echo.
pause