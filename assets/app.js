let currentUser = null;

// Routing
function switchView(view) {
    document.querySelectorAll('[data-view]').forEach(el => el.classList.remove('active'));
    document.querySelector(`[data-view="${view}"]`).classList.add('active');
    
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    let navBtn = document.querySelector(`.nav-item[onclick*="${view}"]`);
    if(navBtn) navBtn.classList.add('active');

    window.scrollTo(0,0);
    
    if(view === 'feed') loadPosts();
    if(view === 'profile') loadProfile();
    if(view === 'admin') loadAdminDashboard();
}

function showAlert(formId, msg, type) {
    const box = document.querySelector(`#${formId} .alert`);
    box.textContent = msg;
    box.className = `alert ${type}`;
    setTimeout(() => box.style.display = 'none', 3000);
}

// Auth
async function checkAuth() {
    let res = await fetch('api/me.php');
    currentUser = await res.json();
    document.getElementById('nav-profile').style.display = currentUser ? 'flex' : 'none';
    document.getElementById('nav-login').style.display = currentUser ? 'none' : 'flex';
    document.getElementById('nav-admin').style.display = (currentUser && currentUser.role === 'admin') ? 'flex' : 'none';
    if(!currentUser && (document.querySelector('[data-view].active').dataset.view === 'profile' || document.querySelector('[data-view].active').dataset.view === 'admin')) {
        switchView('home');
    }
}

// Setup Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    switchView('home');

    // Forms
    setupForm('loginForm', 'api/login.php', async (res) => {
        await checkAuth();
        switchView(currentUser.role === 'admin' ? 'admin' : 'feed');
    });

    setupForm('signupForm', 'api/signup.php', async () => {
        await checkAuth();
        switchView('profile');
    });

    setupForm('postForm', 'api/create-post.php', () => {
        document.getElementById('postForm').reset();
        switchView('feed');
    });

    setupForm('feedbackForm', 'api/feedback.php', () => {
        document.getElementById('feedbackForm').reset();
        showAlert('feedbackForm', 'Feedback sent! Thank you.', 'success');
    });

    setupForm('profileForm', 'api/update-profile.php', () => showAlert('profileForm', 'Profile updated', 'success'));
});

function setupForm(id, url, onSuccess) {
    const form = document.getElementById(id);
    if(!form) return;
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const data = Object.fromEntries(fd.entries());
        try {
            let res = await fetch(url, { method: 'POST', body: JSON.stringify(data), headers:{'Content-Type':'application/json'} });
            let json = await res.json();
            if(json.error) showAlert(id, json.error, 'error');
            else { if(onSuccess) onSuccess(json); }
        } catch(err) { showAlert(id, 'Network error', 'error'); }
    });
}

function togglePassword(inputId) {
    const inp = document.getElementById(inputId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

async function logout() {
    await fetch('api/logout.php');
    currentUser = null;
    await checkAuth();
    switchView('home');
}

// Feed & Posts
async function loadPosts() {
    const res = await fetch('api/get-posts.php');
    const posts = await res.json();
    const c = document.getElementById('feed-container');
    c.innerHTML = '';
    posts.forEach(p => {
        const dp = p.profile_picture ? `uploads/profiles/${p.profile_picture}` : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><circle cx="20" cy="20" r="20" fill="%23ccc"/></svg>';
        const badge = p.is_official ? `<span class="official-badge">✓ Official</span>` : '';
        const img = p.image_url ? `<img src="uploads/posts/${p.image_url}" class="post-img">` : '';
        const waLink = `https://wa.me/${p.whatsapp_phone}?text=${encodeURIComponent(p.whatsapp_message)}`;
        
        let adminTools = '';
        if(currentUser && currentUser.role === 'admin') {
            adminTools = `<button onclick="adminDeletePost(${p.id})" class="btn btn-danger" style="padding:5px; font-size:12px;">Delete</button>`;
            if(p.is_deleted == 1) adminTools += ` <button onclick="adminRestorePost(${p.id})" class="btn btn-secondary" style="padding:5px; font-size:12px;">Restore</button>`;
        }

        c.innerHTML += `
            <div class="card post-card" ${p.is_deleted == 1 ? 'style="opacity:0.5"' : ''}>
                <div class="post-header">
                    <img src="${dp}" class="post-avatar">
                    <div class="post-meta">
                        <h4 onclick="viewProfile('${p.username}')">${p.name} ${badge}</h4>
                        <small>@${p.username} - ${p.category || p.post_type}</small>
                    </div>
                </div>
                <h3>${p.title}</h3>
                <p>${p.body}</p>
                ${img}
                ${p.whatsapp_phone ? `<a href="${waLink}" target="_blank" onclick="track('whatsapp_click', 'post', ${p.id})" class="btn btn-wa">Contact on WhatsApp</a>` : ''}
                ${adminTools}
            </div>
        `;
    });
}

function viewProfile(username) {
    // Implement simple alert or custom modal for profile view
    fetch(`api/get-profile.php?username=${username}`)
    .then(r=>r.json()).then(data => {
        if(data.error) return alert('Not found');
        alert(`Profile: ${data.name}\nBio: ${data.bio || 'No bio'}`);
    });
}

async function loadProfile() {
    if(!currentUser) return;
    document.getElementById('prof-name').value = currentUser.name || '';
    document.getElementById('prof-username').value = currentUser.username || '';
    document.getElementById('prof-phone').value = currentUser.phone || '';
    document.getElementById('prof-bio').value = currentUser.bio || '';
}

async function uploadDp(input) {
    if(!input.files[0]) return;
    const fd = new FormData();
    fd.append('image', input.files[0]);
    let res = await fetch('api/upload-profile-picture.php', { method: 'POST', body: fd });
    let json = await res.json();
    if(json.success) alert('Uploaded successfully'); else alert(json.error);
}

function track(event, type, id) {
    fetch('api/analytics-track.php', { method: 'POST', body: JSON.stringify({event, target_type: type, target_id: id}), headers:{'Content-Type':'application/json'} });
}

// Admin
async function loadAdminDashboard() {
    let res = await fetch('api/admin-analytics.php');
    if(!res.ok) return switchView('home');
    let stats = await res.json();
    document.getElementById('admin-stats').innerHTML = `
        <div class="stat-box"><h3>${stats.users}</h3><p>Users</p></div>
        <div class="stat-box"><h3>${stats.posts}</h3><p>Active Posts</p></div>
        <div class="stat-box"><h3>${stats.new_users_24h}</h3><p>New Users (24h)</p></div>
        <div class="stat-box"><h3>${stats.clicks}</h3><p>WA Clicks</p></div>
    `;
    let fbRes = await fetch('api/admin-feedback.php');
    let fb = await fbRes.json();
    document.getElementById('admin-feedback-list').innerHTML = fb.map(f => `<div class="card"><p><strong>${f.name || 'Anon'}</strong> (${f.email || 'No email'}): ${f.message}</p></div>`).join('');
}

async function adminDeletePost(id) {
    if(confirm('Delete post?')) {
        await fetch('api/admin-delete-post.php', { method:'POST', body: JSON.stringify({id}), headers:{'Content-Type':'application/json'}});
        loadPosts();
    }
}
async function adminRestorePost(id) {
    await fetch('api/admin-restore-post.php', { method:'POST', body: JSON.stringify({id}), headers:{'Content-Type':'application/json'}});
    loadPosts();
}

document.getElementById('adminPostForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    let fd = new FormData(e.target);
    await fetch('api/admin-create-official-post.php', { method: 'POST', body: fd });
    e.target.reset();
    alert('Official post created');
    loadPosts();
});
