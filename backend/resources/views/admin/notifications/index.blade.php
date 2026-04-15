@extends('admin.layout')

@section('title', 'Notifications Hub')

@section('content')
    <div class="grid-4">
        <!-- Send Form -->
        <div class="card" style="grid-column: span 2;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0;">Send Push Notification</h3>
                <div id="emoji-picker" style="position: relative;">
                    <button type="button" onclick="toggleEmojiPicker()" class="btn" style="background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: 99px; font-size: 0.8rem;">😊 Add Emoji</button>
                    <div id="emoji-list" style="display: none; position: absolute; top: 100%; right: 0; background: var(--card-bg); border: 1px solid var(--border); border-radius: 1rem; padding: 1rem; width: 250px; z-index: 100; box-shadow: 0 10px 30px rgba(0,0,0,0.5); backdrop-filter: blur(20px); margin-top: 0.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.5rem; text-align: center;">
                            @php 
                                $emojis = ['🚀', '✨', '⚡', '🔥', '🎉', '🎁', '🔔', '📣', '📢', '🐶', '🐾', '❤️', '✅', '⚠️', '⭐', '🎈', '🆕', '💥', '📱', '💰', '🛒', '🛒', '🛍️', '🐕', '🐩', '🏠', '📍', '🕒', '📅', '🎁'];
                            @endphp
                            @foreach($emojis as $emoji)
                                <span onclick="insertEmoji('{{ $emoji }}')" style="cursor: pointer; font-size: 1.25rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.3)'" onmouseout="this.style.transform='scale(1)'">{{ $emoji }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.notifications.send') }}" method="POST">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label>Target User</label>
                    <select name="user_id" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 1rem; color: white;">
                        <option value="">All Registered Users (Broadcast)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <label>Title (EN)</label>
                        <input type="text" name="title" id="notif_title" required placeholder="Catchy title..." onfocus="setLastFocused(this)" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 0.75rem; color: white;">
                    </div>
                    <div style="flex: 1;">
                        <label style="direction: rtl; display: block;">العنوان (AR)</label>
                        <input type="text" name="title_ar" id="notif_title_ar" placeholder="عنوان جذاب..." onfocus="setLastFocused(this)" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 0.75rem; color: white; direction: rtl;">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                    <div style="flex: 1;">
                        <label>Message (EN)</label>
                        <textarea name="body" id="notif_body" required rows="3" placeholder="Notification text..." onfocus="setLastFocused(this)" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 0.75rem; color: white;"></textarea>
                    </div>
                    <div style="flex: 1;">
                        <label style="direction: rtl; display: block;">الرسالة (AR)</label>
                        <textarea name="body_ar" id="notif_body_ar" rows="3" placeholder="نص الإشعار..." onfocus="setLastFocused(this)" style="width: 100%; padding: 0.75rem; background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 0.75rem; color: white; direction: rtl;"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1.25rem; font-size: 1rem; font-weight: 600; border-radius: 1rem;">🚀 Send Notification</button>
            </form>
        </div>
    </div>

    <script>
        let lastFocused = document.getElementById('notif_title');

        function setLastFocused(el) {
            lastFocused = el;
        }

        function toggleEmojiPicker() {
            const list = document.getElementById('emoji-list');
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
        }

        function insertEmoji(emoji) {
            const el = lastFocused;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = el.value;
            el.value = text.substring(0, start) + emoji + text.substring(end);
            el.focus();
            el.selectionStart = el.selectionEnd = start + emoji.length;
        }

        // Close picker when clicking outside
        document.addEventListener('click', function(e) {
            const picker = document.getElementById('emoji-picker');
            if (!picker.contains(e.target)) {
                document.getElementById('emoji-list').style.display = 'none';
            }
        });
    </script>

    <!-- History -->
    <div class="card" style="margin-top: 2rem;">
        <form id="bulk-delete-form" action="{{ route('admin.notifications.bulk_delete') }}" method="POST">
            @csrf
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0;">Notification History</h3>
                <button type="submit" id="bulk-delete-btn" class="btn btn-secondary" style="background: #ef4444; border: none; display: none; font-size: 0.8rem; padding: 0.5rem 1rem;">
                    🗑️ Delete Selected (<span id="selected-count">0</span>)
                </button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="select-all" style="width: 18px; height: 18px;"></th>
                        <th>Type</th>
                        <th>User ID</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($notifications as $notif)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $notif->id }}" class="notif-checkbox" style="width: 18px; height: 18px;"></td>
                    <td><span class="badge" style="background: rgba(139, 92, 246, 0.2); color: var(--primary);">{{ ucfirst($notif->type ?? 'System') }}</span></td>
                    <td>#{{ $notif->user_id }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $notif->title_en ?? '' }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); direction: rtl;">{{ $notif->title_ar ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: var(--text); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $notif->message_en ?? '' }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; direction: rtl;">
                            {{ $notif->message_ar ?? '' }}
                        </div>
                    </td>
                    <td><span style="font-size: 0.8rem;">{{ $notif->created_at->diffForHumans() }}</span></td>
                    <td>
                         <form action="{{ route('admin.notifications.destroy', $notif->id) }}" method="POST" onsubmit="return confirm('Delete?');" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-icon" style="background:none; border:none; cursor:pointer; font-size:1.2rem;">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </form>
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $notifications->links() }}
        </div>
    </div>

    <script>
        // Select All Logic
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.notif-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCount = document.getElementById('selected-count');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkBtn();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkBtn);
        });

        function updateBulkBtn() {
            const checked = document.querySelectorAll('.notif-checkbox:checked').length;
            selectedCount.textContent = checked;
            bulkDeleteBtn.style.display = checked > 0 ? 'flex' : 'none';
        }

        document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete ' + document.querySelectorAll('.notif-checkbox:checked').length + ' items?')) {
                e.preventDefault();
            }
        });
    </script>
@endsection
