# Organization Plan for raindrops-web

## Current State
- 23 files across 3 folders.
- Mixed root: 5 PHP scripts, 3 HTML files, CSS, JS, and config files all in one place.
- Issues: No clear separation of concerns; backend and frontend logic are interleaved.

## Proposed Structure
```
raindrops-web/
├── assets/             (Images, icons, logos)
├── src/
│   ├── css/            (Stylesheets)
│   ├── js/             (Frontend logic)
│   └── php/            (Backend API and scripts)
├── docs/               (RAILWAY_SETUP.md, README.md)
├── data/               (Persistent JSON storage - DO NOT MOVE)
├── index.html          (Main Entry)
├── appeal.html         (Entry)
├── staff.html          (Entry)
└── Dockerfile          (Deployment config)
```

## Changes I'll Make
1. **Create folders**: `src/css`, `src/js`, `src/php`, `docs`.
2. **Move files**:
   - `index.css` &rarr; `src/css/index.css`
   - `index.js` &rarr; `src/js/index.js`
   - `get_appeals.php`, `submit_appeal.php`, etc. &rarr; `src/php/`
   - `RAILWAY_SETUP.md` &rarr; `docs/`
3. **Update References**:
   - Update HTML links to the new CSS/JS paths.
   - Update JS `fetch()` calls to target the new PHP locations in `src/php/`.

## Files Needing Your Decision
- **index.html**: Recommended to stay at root to keep the Railway deployment simple. Moving it would require changing the `-t` root in `Dockerfile`.

Ready to proceed? (yes/no/modify)
