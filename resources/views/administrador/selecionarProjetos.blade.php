@extends('layouts.app')

@section('content')

<div class="container" style="margin-top: 30px;">


  <div class="container" >
    <div class="row justify-content-center" style="margin-bottom: 50px;">
      <div class="col-md-1">
        <a href="{{ route('admin.atribuir', ['evento_id' => $evento->id]) }}" class="btn btn-secondary">
          Voltar
        </a>
      </div>
      <div class="col-md-10" style="text-align: center;">
        <h3  class="titulo-table">Lista de Projetos do Edital: <span style="color: black;">{{ $evento->nome }}</span> </h3>
      </div>
      <div class="col-md-1">
        <!-- Button trigger modal -->
        {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter">
          Enviar Convite
        </button> --}}
      </div>
    </div>
    <div class="row">
      <div class="col-md-8">
        <div class="row">
          <div class="col-sm-1">
            <button class="btn" onclick="buscar(this.parentElement.parentElement.children[1].children[0])">
              <img src="{{asset('img/icons/logo_lupa.png')}}" alt="">
            </button>
          </div>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-edit" placeholder="Digite o nome do projeto" onkeyup="buscar(this)">
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th scope="col">Nome do Projeto</th>
        <th scope="col">Área</th>
        <th scope="col">Proponente</th>
        <th scope="col" style="text-align:center">Ação</th>
      </tr>
    </thead>
    <tbody id="projetos">
      @foreach ($trabalhos as $trabalho)
          <tr>
            <td style="max-width:100px; overflow-x:auto; text-overflow:ellipsis">{{ $trabalho->titulo}}</td>
            <td>{{ App\Area::find($trabalho->area_id)->nome}}</td>
            <td>{{ $trabalho->proponente->user->name }}</td>
            <td style="text-align:center">
                <button type="button" class="btn btn-primary" value="{{ $trabalho->id }}" id="atribuir1" data-toggle="modal" data-target="#exampleModalCenter{{ $trabalho->id }}">
                  Atribuir
                </button>
                <!-- Modal -->
                <div class="modal fade" id="exampleModalCenter{{ $trabalho->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content modal-submeta">
                      <div class="modal-header modal-header-submeta">
                        <h5 class="modal-title titulo-table" id="exampleModalLongTitle">Selecione o(s) avaliador(es)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"  style="color: rgb(182, 182, 182)">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">

                        <form action="{{ route('admin.atribuicao.projeto') }}" method="POST">
                          @csrf
                          <input type="hidden" name="trabalho_id" value="{{ $trabalho->id }}">
                          <input type="hidden" name="evento_id" value="{{ $evento->id }}">
                          <div class="form-group text-left">
                            <div class="row" style="margin-left: 2px;margin-bottom: 1px">

                              <div class="col-md-4">
                                <label for="exampleFormControlSelect2"
                                       style="font-size: 16px;">Selecione o(s) avaliador(es)
                                  para esse projeto</label>
                              </div>


                              <div class="col-md-3"
                                   style="text-align: center;overflow-y:  auto;overflow-x:  auto">

                                <select class="form-control" id="grandeArea"
                                        name="grande_area_id" onchange="areasFiltro()">
                                  <option value="" disabled selected hidden>-- Grande Área
                                    --
                                  </option>
                                  @foreach($grandesAreas as $grandeArea)
                                    <option title="{{$grandeArea->nome}}"
                                            value="{{$grandeArea->id}}">{{$grandeArea->nome}}</option>
                                  @endforeach
                                </select>
                              </div>

                              <div class="col-md-2"
                                   style="text-align: center;overflow-y:  auto;overflow-x:  auto">
                                <input type="hidden" id="oldArea" value="{{ old('area') }}">
                                <select class="form-control @error('area') is-invalid @enderror"
                                        id="area" name="area_id"
                                        onchange="(consultaExterno(),consultaInterno())">
                                  <option value="" disabled selected hidden>-- Área --
                                  </option>
                                </select>
                              </div>

                              <div class="col-sm-3" style="display:flex; align-items: end;">
                                <input type="text" class="form-control form-control-edit" placeholder="Nome do avaliador" onkeyup="buscar(this)" style="max-width: 200px;"> <img src="{{asset('img/icons/logo_lupa.png')}}" alt="">
                              </div>

                            </div>

                            <div class="col-md-6">
                              <label style="font-weight: bold;font-size: 18px">Internos</label>
                            </div>
                            <input type="hidden" id="oldAvalInterno"
                                   value="{{ old('exampleFormControlSelect2') }}">
                            <select name="avaliadores_internos_id[]" multiple
                                    class="form-control" id="exampleFormControlSelect2"
                                    style="height: 200px;font-size: 15px">

                              @foreach ($trabalho->avaliadors as $avaliador)
                                @if(($avaliador->tipo == "Interno" && $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == 1) ||
                                    (($avaliador->user->instituicao == "UFAPE" || $avaliador->user->instituicao == "Universidade Federal do Agreste de Pernambuco") && ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null || $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == 1) ))
                                  <option value="{{ $avaliador->id }}">{{ $avaliador->user->name }}
                                    > {{$avaliador->user->instituicao ?? 'Instituição Indefinida'}}
                                    > {{$avaliador->area->nome ?? 'Indefinida'}}
                                    > {{$avaliador->user->email}}</option>
                                @endif
                              @endforeach
                              @foreach ($trabalho->aval as $avaliador)
                                @if($avaliador->tipo == "Interno" || $avaliador->user->instituicao == "UFAPE" || $avaliador->user->instituicao == "Universidade Federal do Agreste de Pernambuco")
                                  <option value="{{ $avaliador->id }}"> {{ $avaliador->user->name }}
                                    > {{$avaliador->user->instituicao ?? 'Instituição Indefinida'}}
                                    > {{$avaliador->area->nome ?? 'Indefinida'}}
                                    > {{$avaliador->user->email}}</option>
                                @endif
                              @endforeach
                            </select>


                            <div class="col-md-6">
                              <label style="font-weight: bold;font-size: 18px"><i>Ad Hoc</i></label>
                            </div>

                            <input type="hidden" id="trab" value="{{$trabalho->id}}">
                            <input type="hidden" id="oldAvalExterno"
                                   value="{{ old('exampleFormControlSelect3') }}">
                            <select name="avaliadores_externos_id[]" multiple
                                    class="form-control" id="exampleFormControlSelect3"
                                    style="height: 200px;font-size:15px">
                              @foreach ($trabalho->avaliadors as $avaliador)
                                @if($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == 2 || ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null && $avaliador->tipo == "Interno"))
                                  <option value="{{ $avaliador->id }}">{{ $avaliador->user->name }}
                                    > {{$avaliador->user->instituicao ?? 'Instituição Indefinida'}}
                                    > {{$avaliador->area->nome ?? 'Indefinida'}}
                                    > {{$avaliador->user->email}}</option>
                                @endif
                              @endforeach
                              @foreach ($trabalho->aval as $avaliador)
                                <option value="{{ $avaliador->id }}"> {{ $avaliador->user->name }}
                                  > {{$avaliador->user->instituicao ?? 'Instituição Indefinida'}}
                                  > {{$avaliador->area->nome ?? 'Indefinida'}}
                                  > {{$avaliador->user->email}}</option>
                              @endforeach
                            </select>

                            <small id="emailHelp" class="form-text text-muted">Segure SHIFT do
                              teclado para selecionar mais de um.</small>
                          </div>

                          <div>
                            <button type="submit" class="btn btn-info" style="width: 100%">
                              Atribuir
                            </button>
                          </div>

                        </form>

                      </div>
                    </div>
                  </div>
                </div>
            </td>
          </tr>
      @endforeach
    </tbody>
  </table>

  <div class="container" style="margin-top: 50px;">
    <div class="row justify-content-center d-flex align-items-center" >

        <h3 class="titulo-table">Status dos Projetos em Avaliação do edital: <span style="color: black;">{{ $evento->nome }}</span> </h3>

    </div>
  </div>
  <hr>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th scope="col">Nome do Usuário</th>
        <th scope="col">Tipo de Avaliação</th>
        <th scope="col">E-mail</th>
        <th scope="col">Titulo do projeto</th>
        <th scope="col">Status avaliação</th>
        <th scope="col" style="text-align:center">Ação</th>
      </tr>
    </thead>
    <tbody>
      @foreach($trabalho->avaliadors as $avaliador)
      {{-- Avaliação Interna --}}
        @if(($avaliador->tipo == 'Interno' && ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null || $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso != 1))
                                   || (($avaliador->user->instituicao == "UFAPE" || $avaliador->user->instituicao == "Universidade Federal do Agreste de Pernambuco") && $avaliador->tipo == null && ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null || $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso != 1)))
          <tr>
            <td>{{ $avaliador->user->name }}</td>
            <td> Interno </td>
            <td>{{ $avaliador->user->email }}</td>
            <td style="max-width:100px; overflow-x:hidden; text-overflow:ellipsis">{{ $trabalho->titulo }}</td>
            @php
              $parecerInterno = App\ParecerInterno::where([['avaliador_id',$avaliador->id],['trabalho_id',$trabalho->id]])->first();
            @endphp
            <td>@if($parecerInterno == null) Pendente @else Avaliado @endif</td>
            <td>
              <div class="btn-group dropright dropdown-options">
                <a id="options" class="dropdown-toggle " data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <img src="{{asset('img/icons/ellipsis-v-solid.svg')}}" style="width:8px">
                </a>
                <div class="dropdown-menu">
                  @if($parecerInterno != null)
                    <a href="{{ route('admin.visualizarParecerInterno', ['trabalho_id' => $trabalho->id, 'avaliador_id' => $avaliador->id]) }}" class="dropdown-item text-center">
                      Vizualizar Parecer
                    </a>
                  @endif
                  <a href="{{ route('admin.removerProjAval', ['trabalho_id' => $trabalho->id, 'avaliador_id' => $avaliador->id,'flag'=>1]) }}" class="dropdown-item text-center">
                    Desatribuir Avaliador
                  </a>

                </div>
              </div>
            </td>
          </tr>
        @endif

      {{-- Avaliação Ad Hoc --}}
      @if( ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null && $avaliador->tipo == "Externo") || $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso != 2
                                    || (($avaliador->user->instituicao != "UFAPE" && $avaliador->user->instituicao != "Universidade Federal do Agreste de Pernambuco") && $avaliador->tipo == null && ($avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso == null || $avaliador->trabalhos()->where("trabalho_id",$trabalho->id)->first()->pivot->acesso != 2)))
        <tr>
            <td>{{ $avaliador->user->name }}</td>
            <td> Ad Hoc </td>
            <td>{{ $avaliador->user->email }}</td>
            <td style="max-width:100px; overflow-x:hidden; text-overflow:ellipsis">{{ $trabalho->titulo }}</td>
            <td>@if($avaliador->trabalhos->where('id', $trabalho->id)->first()->pivot->status == false) Pendente @else Avaliado @endif</td>
            <td>
              <div class="btn-group dropright dropdown-options">
                <a id="options" class="dropdown-toggle " data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <img src="{{asset('img/icons/ellipsis-v-solid.svg')}}" style="width:8px">
                </a>
                <div class="dropdown-menu">
                  @if($avaliador->trabalhos->where('id', $trabalho->id)->first()->pivot->status == true)
                    <a href="{{ route('admin.visualizarParecer', ['trabalho_id' => $trabalho->id, 'avaliador_id' => $avaliador->id]) }}" class="dropdown-item text-center">
                      Vizualizar Parecer
                    </a>
                  @endif
                  <a href="{{ route('admin.removerProjAval', ['trabalho_id' => $trabalho->id, 'avaliador_id' => $avaliador->id,'flag'=>0]) }}" class="dropdown-item text-center">
                    Desatribuir Avaliador
                  </a>

                </div>
              </div>
            </td>
          </tr>
        @endif


      @endforeach

    </tbody>
  </table>

</div>


<!-- Button trigger modal -->




@endsection

@section('javascript')
<script>
  $('#myModal').on('shown.bs.modal', function () {
    $('#myInput').trigger('focus')
  });

  function buscar(input) {
    var editais = document.getElementById('projetos').children;
    if(input.value.length > 2) {      
      for(var i = 0; i < editais.length; i++) {
        var nomeEvento = editais[i].children[0].textContent;
        if(nomeEvento.substr(0).indexOf(input.value) >= 0) {
          editais[i].style.display = "";
        } else {
          editais[i].style.display = "none";
        }
      }
    } else {
      for(var i = 0; i < editais.length; i++) {
        editais[i].style.display = "";
      }
    }
  }
</script>
@endsection
