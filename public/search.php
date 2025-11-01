<?php
// index.php — Shaikhoology Shariah Screener (LIVE API wired)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shaikhoology Shariah Screener</title>
<meta name="theme-color" content="#ffffff">
<style>
  :root{
    --bg:#ffffff; --text:#202124; --muted:#5f6368; --line:#e0e3e7;
    --chip:#f1f3f4; --accent:#1a73e8; --accent-2:#34a853;
    --warn:#fbbc05; --danger:#ea4335; --radius:16px;
    --shadow:0 8px 28px rgba(60,64,67,.18), 0 1px 3px rgba(60,64,67,.12);
  }
  [data-theme="dark"]{
    --bg:#0f1115; --text:#e7e9ee; --muted:#a3a9b4; --line:#222734;
    --chip:#171b22; --accent:#8ab4f8; --accent-2:#60d394;
    --warn:#ffd866; --danger:#ff6b6b;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0; font:16px/1.55 system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial;
    background:var(--bg); color:var(--text);
    display:grid; grid-template-rows:auto 1fr auto;
  }

  /* Header */
  .header{
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 16px; border-bottom:1px solid var(--line);
  }
  .brand{font-weight:800}
  .mode{
    appearance:none; border:1px solid var(--line); background:var(--chip);
    padding:8px 12px; border-radius:999px; color:var(--text); cursor:pointer;
  }

  /* Top progress line */
  .topbar{position:fixed;left:0;top:0;width:100%;height:3px;background:linear-gradient(90deg,transparent,var(--accent),transparent);background-size:200% 100%;display:none;animation:slide 1.05s linear infinite;z-index:1000}
  @keyframes slide{0%{background-position:200% 0}100%{background-position:-200% 0}}

  /* Hero search card */
  .hero{display:flex;align-items:center;justify-content:center;padding:48px 16px 12px}
  .card{
    width:min(860px,96vw); background:var(--bg);
    border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow);
    padding:22px 18px 18px;
  }
  .headline{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:12px}
  .headline h1{font-size:18px;margin:0}
  .badge{font-size:12px;padding:6px 10px;border-radius:999px;background:var(--chip);border:1px solid var(--line);}

  .search-wrap{position:relative;margin:4px auto}
  .ai-glow{position:absolute; inset:-8px; border-radius:999px; filter:blur(18px);
    background:radial-gradient(100% 100% at 50% 50%, rgba(26,115,232,.25), rgba(52,168,83,.18), transparent 60%);
    opacity:0; pointer-events:none; transition:opacity .25s ease}
  .search-wrap.active .ai-glow{opacity:1; animation:breath 1.6s ease-in-out infinite}
  @keyframes breath{0%,100%{transform:scale(.985)}50%{transform:scale(1.01)}}

  .search-row{
    display:flex;gap:8px;align-items:center;
    border:1px solid var(--line); border-radius:999px; padding:6px 8px;
    background:var(--bg); box-shadow:0 1px 2px rgba(0,0,0,.05);
  }
  .search-row input{
    flex:1; border:none; outline:none; background:transparent;
    font-size:18px; padding:12px 12px; min-width:0; color:var(--text);
  }
  .btn{border:none;border-radius:999px;padding:12px 18px;font-weight:700;background:var(--accent);color:#fff;cursor:pointer}
  .btn:disabled{opacity:.65;cursor:default}
  .hint{color:var(--muted);font-size:13px;margin-top:8px;text-align:center}

  @media (max-width:520px){
    .search-row{flex-wrap:wrap}
    .search-row input{width:100%; font-size:16px}
    .btn{width:100%}
  }

  /* Steps */
  .steps{width:min(860px,96vw);margin:10px auto 0;display:none}
  .step{display:flex;align-items:center;gap:12px;border:1px solid var(--line);border-radius:12px;padding:10px 12px;background:var(--bg);margin-bottom:8px}
  .spinner{width:16px;height:16px;border:3px solid #e6eefb;border-top-color:var(--accent);border-radius:50%;animation:spin .85s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
  .check{width:18px;height:18px;border-radius:50%;background:var(--accent-2);display:none;position:relative}
  .check:after{content:"";position:absolute;left:5px;top:2px;width:6px;height:10px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg)}
  .step.done .spinner{display:none}
  .step.done .check{display:inline-block}
  .step .label{font-weight:600}
  .muted{color:var(--muted);font-size:13px}

  /* Results */
  .results{width:min(860px,96vw);margin:14px auto 60px}
  .result{border:1px solid var(--line);border-radius:12px;padding:16px;background:var(--bg);margin-bottom:10px}
  .r-head{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between}
  .title{font-size:18px;font-weight:800}
  .ticker{color:var(--muted);font-weight:600}
  .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
  .chip{background:var(--chip);border:1px solid var(--line);color:var(--text);font-size:12px;padding:6px 10px;border-radius:999px}
  .chip.pass{border-color:rgba(52,168,83,.45);color:#1e5633}
  .chip.fail{border-color:rgba(234,67,53,.45);color:#7b2121}
  .divider{height:1px;background:var(--line);margin:12px 0}
  .pairs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .pair{background:var(--chip);border:1px solid var(--line);border-radius:10px;padding:10px}
  .label{color:var(--muted);font-size:12px}
  .value{font-weight:800;margin-top:4px}
  .meta{color:var(--muted);font-size:12px;margin-top:10px}
  @media (max-width:640px){.pairs{grid-template-columns:1fr}.title{font-size:16px}}

  footer{border-top:1px solid var(--line);padding:14px 16px;text-align:center;color:var(--muted);background:var(--bg)}
</style>
</head>
<body data-theme="light">
<div class="topbar" id="topbar"></div>

<header class="header">
  <div class="brand">Shaikhoology Shariah Screener</div>
  <button class="mode" id="modeBtn">Dark / Light</button>
</header>

<section class="hero">
  <div class="card">
    <div class="headline">
      <h1>Search</h1>
      <span class="badge">Live • DB-backed</span>
    </div>

    <div class="search-wrap" id="searchWrap">
      <div class="ai-glow"></div>
      <form id="searchForm" class="search-row" action="" method="get" role="search" aria-label="Shariah Screener Search">
        <input id="qInput" name="q" type="text"
               placeholder="Search company name or code (e.g., TCS, RELIANCE, SBIN)…"
               value="<?php echo e($q); ?>" autocomplete="off" aria-label="Search query">
        <button class="btn" id="goBtn" type="submit">Search</button>
      </form>
    </div>

    <div class="hint">Beta Mode</div>
  </div>
</section>

<section class="steps" id="steps">
  <div class="step" id="st1"><span class="spinner"></span><span class="check"></span><span class="label">Connecting</span><span class="muted">to database…</span></div>
  <div class="step" id="st2"><span class="spinner"></span><span class="check"></span><span class="label">Fetching</span><span class="muted">company & latest metrics…</span></div>
  <div class="step" id="st3"><span class="spinner"></span><span class="check"></span><span class="label">Rendering</span><span class="muted">final card…</span></div>
</section>

<section class="results" id="results">
  <div id="placeholder"></div>
  <div id="resultsList" aria-live="polite" aria-busy="false"></div>
</section>

<footer>Shaikhoology - Trading Psychology | Since 2021</footer>

<script>
  // === THEME ===
  const body = document.body;
  const modeBtn = document.getElementById('modeBtn');
  (function initTheme(){
    const saved = localStorage.getItem('theme');
    if(saved) body.setAttribute('data-theme', saved);
  })();
  modeBtn.addEventListener('click', ()=>{
    const cur = body.getAttribute('data-theme') || 'light';
    const next = cur === 'light' ? 'dark' : 'light';
    body.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
  });

  // === ELEMENTS ===
  const form = document.getElementById('searchForm');
  const input = document.getElementById('qInput');
  const topbar = document.getElementById('topbar');
  const searchWrap = document.getElementById('searchWrap');
  const steps = document.getElementById('steps');
  const st1 = document.getElementById('st1');
  const st2 = document.getElementById('st2');
  const st3 = document.getElementById('st3');
  const resultsList = document.getElementById('resultsList');
  const placeholder = document.getElementById('placeholder');
  const resultsSection = document.getElementById('results');

  // === STEP HELPERS ===
  function markDone(el){ el.classList.add('done'); }
  function resetSteps(){ [st1,st2,st3].forEach(s=>s.classList.remove('done')); }
  function waitMs(ms){return new Promise(r=>setTimeout(r,ms));}
  function scrollToResults(){ resultsSection.scrollIntoView({behavior:'smooth', block:'start'}); }

  function startUX(){
    resultsList.setAttribute('aria-busy','true');
    topbar.style.display = 'block';
    searchWrap.classList.add('active');
    steps.style.display = 'block';
    resetSteps();
    placeholder.innerHTML = `
      <div class="result">
        <div class="shimmer" style="height:10px;border-radius:6px;background:linear-gradient(90deg,#eef2f7 0%,#e6ebf3 50%,#eef2f7 100%);background-size:180% 100%;animation:shimmer 1.2s infinite"></div>
      </div>`;
    scrollToResults();
  }

  function endUX(){
    resultsList.setAttribute('aria-busy','false');
    topbar.style.display = 'none';
    searchWrap.classList.remove('active');
    placeholder.innerHTML = '';
  }

  // === RENDER ===
  function renderResult(d){
    const pairs = (d.fields||[]).map(f=>`
      <div class="pair">
        <div class="label">${escapeHtml(f.k)}</div>
        <div class="value">${escapeHtml(String(f.v))}</div>
      </div>`).join('');

    const statusChip = (d.status === 'HALAL' || d.status === 'DATA_FOUND')
      ? '<span class="chip pass">PASS</span>'
      : (d.status ? '<span class="chip fail">FAIL</span>' : '');

    resultsList.innerHTML = `
      <article class="result">
        <div class="r-head">
          <div>
            <div class="title">${escapeHtml(d.company||'Unknown')}</div>
            <div class="ticker">${escapeHtml(d.ticker||'--')} • ${escapeHtml(d.updated||'--')}</div>
          </div>
          ${statusChip}
        </div>
        <div class="divider"></div>
        <div class="pairs">${pairs}</div>
        <div class="meta">Status updates quarterly; backend syncs daily. Borderline → Needs Review.</div>
      </article>`;
    scrollToResults();
  }

  function renderError(msg, raw){
    resultsList.innerHTML = `
      <article class="result">
        <div class="title">No result</div>
        <div class="meta" style="color:#b91c1c">${escapeHtml(msg)}</div>
        ${raw ? `<pre class="meta" style="white-space:pre-wrap;margin-top:8px">${escapeHtml(raw)}</pre>` : ''}
      </article>`;
    scrollToResults();
  }

  function escapeHtml(s){return (s||'').toString().replace(/[&<>"']/g,m=>({'&':'&','<':'<','>':'>','"':'"'}[m]))}

  // === SEARCH ===
  async function doSearch(q){
    if(!q) return;
    startUX();

    try{
      markDone(st1);
      await waitMs(200);
      markDone(st2);

      const url = '/api/search.php?q=' + encodeURIComponent(q);
      console.log('[SEARCH] GET', url);
      const res = await fetch(url, {headers:{'Accept':'application/json'}});
      const text = await res.text();
      console.log('[SEARCH] raw', text);

      let data;
      try { data = JSON.parse(text); }
      catch(parseErr){ renderError('Bad JSON from API', text); console.error(parseErr); return; }

      if(!res.ok){
        renderError((data && (data.message||data.error)) ? (data.error+': '+data.message) : ('HTTP '+res.status), text);
        return;
      }

      markDone(st3);
      await waitMs(150);

      if(data.not_found){
        renderError('No company found for "'+ q +'"');
      }else{
        renderResult(data);
      }
    }catch(err){
      console.error(err);
      renderError(err.message || 'Server error');
    }finally{
      endUX();
      // keep q in URL
      const u = new URL(window.location.href);
      u.searchParams.set('q', q);
      history.replaceState(null,'',u.toString());
    }
  }

  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    doSearch(input.value.trim());
  });

  window.addEventListener('DOMContentLoaded', ()=>{
    const initialQ = "<?php echo e($q); ?>";
    if(initialQ){ doSearch(initialQ); }
    input.focus();
  });

  // shimmer keyframes
  const style = document.createElement('style');
  style.textContent = `@keyframes shimmer{0%{background-position:180% 0}100%{background-position:-20% 0}}`;
  document.head.appendChild(style);
</script>
</body>
</html>