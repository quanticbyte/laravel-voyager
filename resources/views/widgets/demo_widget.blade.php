<div class="panel widget center bgimage" style="margin-bottom:0;overflow:hidden;background-image:url('{{ $image }}');">
    <div class="dimmer"></div>
    <div class="panel-content" style='padding-bottom: 60px;'>
        @if (isset($icon))<i class='{{ $icon }}'></i>@endif
        <h4>Peticiones {!! $title !!}</h4>
        <p>
        <b> {!! $per_contestar !!} </b> Peticiónes por contestar<br>
        <b> {!! $contestades !!} </b> Peticiónes contestadas<br>
        <b> {!! $acceptades !!} </b> Peticiónes acceptadas<br>
        <b> {!! $denegades !!} </b> Peticiónes denegadas
        </p>

    </div>
</div>
