@php
    $acceptUrl = route('invites.accept', $invite->token);
@endphp
<p>Hello,</p>
<p>You have been invited to join the sales system. This invite expires in 24 hours.</p>
<p>
    <a href="{{ $acceptUrl }}">Accept your invite</a>
</p>
<p>If you did not expect this, you can ignore this email.</p>
