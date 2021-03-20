<tr>
<td class="header">
<a href="{{ $url }}">
<!-- give Outlook a smaller image, and a different size to other displays...
From: https://gist.github.com/jamesmacwhite/18e97b06f2c04661a757
Note: <div> wrapping SBS_Logo !
 -->
<!--[if mso]>
    <img src="{{ asset('img/SBS_Logo_sm.png') }}" alt="Logo">
    <div style="display:none">
<![endif]-->
    <img src="{{ asset('img/SBS_Logo.png') }}" class="logo" alt="Logo" style="mso-hide:all;">
<!--[if mso]>
    </div>
<![endif]-->    
</a>
</td>
</tr>