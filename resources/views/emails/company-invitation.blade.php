<h2>You are invited to join a company</h2>

<p>
    You have been invited to join company:
    <strong>{{ $invitation->company->name }}</strong>
</p>

<p>Role: {{ $invitation->role->name }}</p>

<a href="{{ $acceptUrl }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
    Accept Invitation
</a>

<p style="font-size: 0.8em; color: #666;">
    This link will expire in 3 days.
</p>

<br><br>

<a href="{{ url('/api/invitations/decline/'.$invitation->token) }}" style="color: #f44336;">
    Decline Invitation
</a>
