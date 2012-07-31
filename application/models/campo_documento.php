<?php

class CampoDocumento extends Campo {

    public $requiere_validacion = false;
    public $requiere_datos = false;
    public $requiere_readonly = false;

    protected function display($modo, $dato, $etapa_id) {
        if (!$etapa_id) {
            return '<a href="#">' . $this->etiqueta . '</a>';
        }

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            Doctrine_Manager::connection()->beginTransaction();
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $filename = $this->Documento->generar($etapa->Tramite->id);
            
            $file=new File();
            $file->tramite_id=$etapa->Tramite->id;
            $file->filename=$filename;
            $file->tipo='documento';
            $file->save();
            
            $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($etapa->Tramite->id, $this->nombre);
            if (!$dato)
                $dato = new Dato();
            $dato->nombre = $this->nombre;
            $dato->valor = $filename;
            $dato->tramite_id = $etapa->Tramite->id;
            $dato->save();
            Doctrine_Manager::connection()->commit();
        }

        $display = '<p><a href="' . site_url('documentos/get/' . $dato->valor) . '">' . $this->etiqueta . '</a></p>';
        //$display='<p><a href="'.site_url('documentos/ver/'.$this->documento_id.'/'.$etapa_id).'">'.$this->etiqueta.'</a></p>';

        return $display;
    }
    
    public function getReadonly(){
        return 1;
    }

    public function setReadonly($readonly) {
        $this->_set('readonly', 1);
    }
    
    public function backendExtraFields() {
        $html='<label>Documento</label>';
        $html.='<select name="documento_id">';
        $html.='<option value=""></option>';
        foreach($this->Formulario->Proceso->Documentos as $d)
            $html.='<option value="'.$d->id.'" '.($this->documento_id==$d->id?'selected':'').'>'.$d->nombre.'</option>';
        $html.='</select>';
        
        return $html;
    }
    
    public function backendExtraValidate() {
        parent::backendExtraValidate();
        
        $CI= &get_instance();
        $CI->form_validation->set_rules('documento_id','Documento','required');
    }

}