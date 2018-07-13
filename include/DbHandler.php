<?php

class DbHandler
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    //BSS

    public function get_codes_for_sync()
    {

        $stmt = $this->conn->prepare("SELECT id_code, sended, used, id_art, id_user, id_com FROM code");
        //$stmt->bind_param("ii", $offset, $rows_per_page);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;

    }

    public function get_bss_user_data_today()
    {

        $stmt = $this->conn->prepare("SELECT id_prov AS prov, COUNT(*) AS cant FROM user WHERE 
        DATE(created_at) = CURRENT_DATE GROUP BY id_prov");
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;

    }

    public function get_bss_arts_data_today()
    {

        $stmt = $this->conn->prepare("SELECT id_prov AS prov, COUNT(*) AS cant FROM art WHERE 
        DATE(created_at) = CURRENT_DATE GROUP BY id_prov");
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;

    }

    public function get_date_from_timestamp($created_at)
    {

        $timestamp = strtotime($created_at);

        return date('Y-m-d', $timestamp);

    }


    //CONTADORES

    //count para boss arts top
    public function get_count_arts_top_by_prov_and_dep($id_prov, $is_top, $id_dep)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_prov = ? AND is_top = ? AND id_dep = ?");
        $stmt->bind_param("iii", $id_prov, $is_top, $id_dep);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //count para total users
    public function get_count_users_by_prov($id_prov)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM user WHERE id_prov = ?");
        $stmt->bind_param("i", $id_prov);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //count para reportes
    public function get_count_reps()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rep");
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //count for sex users
    public function get_count_users_sex_by_prov($id_prov, $sex)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM user WHERE id_prov = ? AND sex = ?");
        $stmt->bind_param("is", $id_prov, $sex);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador por provincias para la portada
    public function get_count_arts_prem_by_prov1($id_prov, $is_prem)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_prov = ? AND is_prem = ?");
        $stmt->bind_param("ii", $id_prov, $is_prem);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador por provincias y departamento para bss
    public function get_count_arts_prem_by_prov_and_dep_for_bss($id_prov, $id_dep, $is_prem)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_prov = ? AND id_dep = ? AND is_prem = ?");
        $stmt->bind_param("iii", $id_prov, $id_dep, $is_prem);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para los listados
    public function get_count_for_cat($id_dep, $id_cat, $id_prov)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_dep = ? AND id_cat = ? AND id_prov = ?");
        $stmt->bind_param("iii", $id_dep, $id_cat, $id_prov);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para mis anuncios publicados
    public function get_count_for_mis_arts_pub($id_user, $is_prem)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_user = ? AND is_prem = ? AND is_top = ?");
        $stmt->bind_param("iii", $id_user, $is_prem, $is_prem);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para mis aunucios premium
    public function get_count_for_mis_arts_prem($id_user, $buy)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM prem WHERE id_user = ? AND buy = ?");
        $stmt->bind_param("ii", $id_user, $buy);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para mis anuncios top
    public function get_count_for_mis_arts_top($id_user, $buy)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM top WHERE id_user = ? AND buy = ?");
        $stmt->bind_param("ii", $id_user, $buy);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para los anuncios user contact
    public function get_count_arts_user_contact($id_user)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para verificar si se lanza el fragment user des
    public function get_count_for_art_des($id_art)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art_des WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para la lista de deseos
    public function get_count_for_list_des($id_user)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art_des WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para busqueda sin precio por categoria
    public function get_count_search_no_price($id_dep, $id_cat, $id_prov, $word)
    {
        $word_aux = "%" . $word . "%";
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND (title LIKE \"$word_aux\" OR body LIKE \"$word_aux\")");
        $stmt->bind_param("iii", $id_dep, $id_cat, $id_prov);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para busqueda en portada
    public function get_count_search_port($id_prov, $word)
    {
        $word_aux = "%" . $word . "%";
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE id_prov = ? AND (title LIKE \"$word_aux\" OR body LIKE \"$word_aux\")");
        $stmt->bind_param("i", $id_prov);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //contador para busqueda con precio
    public function get_count_cat_search_price($id_dep, $id_cat, $id_prov, $word, $desde, $hasta)
    {
        $word_aux = "%" . $word . "%";
        //CON AMBOS RANGOS

        if ($desde != "0" && $hasta != "0") {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND (title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND ( price >= ? AND price <= ? )");
            $stmt->bind_param("iiiss", $id_dep, $id_cat, $id_prov, $desde, $hasta);
            if ($stmt->execute()) {
                $stmt->bind_result($cant);
                $stmt->fetch();
                $stmt->close();
                return $cant;
            } else {
                return NULL;
            }
        }
        //A PARTIR DE
        if ($desde != "0" && $hasta == "0") {
            $stmt = $this->conn->prepare("SELECT COUNT(*) from art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND (title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND price >= ?");
            $stmt->bind_param("iiis", $id_dep, $id_cat, $id_prov, $desde);
            if ($stmt->execute()) {
                $stmt->bind_result($cant);
                $stmt->fetch();
                $stmt->close();
                return $cant;
            } else {
                return NULL;
            }
        }

        //HASTA
        if ($desde == "0" && $hasta != "0") {
            $stmt = $this->conn->prepare("SELECT COUNT(*) from art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND price <= ?");
            $stmt->bind_param("iiis", $id_dep, $id_cat, $id_prov, $hasta);
            if ($stmt->execute()) {
                $stmt->bind_result($cant);
                $stmt->fetch();
                $stmt->close();
                return $cant;
            } else {
                return NULL;
            }
        }
    }

    //get count para reportes de usuario
    public function get_count_rep_user($id_user)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rep WHERE id_user = ? ");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($cant);
            $stmt->fetch();
            $stmt->close();
            return $cant;
        } else {
            return NULL;
        }
    }

    //GETS

    //data en boss para top
    public function get_data_top_for_bss($id_art)
    {
        $stmt = $this->conn->prepare("SELECT date_begin, date_end, cant_days, buy FROM top WHERE  id_art = ? ");
        $stmt->bind_param("i", $id_art);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($date_begin, $date_end, $cant_days, $buy);
            $stmt->fetch();
            $res["date_begin"] = $date_begin;
            $res["date_end"] = $date_end;
            $res["cant_days"] = $cant_days;
            $res["buy"] = $buy;

            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    //get arts for top
    public function get_top_for_bss($id_prov, $id_dep, $is_top, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE  id_prov = ? AND is_top = ? AND id_dep = ? ORDER BY prior ASC, created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iiiii", $id_prov, $is_top, $id_dep, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    //get users for bss
    public function get_users($id_prov, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE  id_prov = ? ORDER BY created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iii", $id_prov, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    //get reps
    public function get_reportes($offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rep ORDER BY created_at DESC LIMIT ? , ?");
        $stmt->bind_param("ii", $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    public function get_arts_for_choice_prem($id_user, $is_prem)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_user = ? AND is_prem = ?) ORDER BY created_at DESC");
        $stmt->bind_param("ii", $id_user, $is_prem);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;
    }

    public function get_arts_for_choice_top($id_user, $is_top)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_user = ? AND is_top = ?) ORDER BY created_at DESC");
        $stmt->bind_param("ii", $id_user, $is_top);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;
    }

    public function get_all_codes()
    {

        $stmt = $this->conn->prepare("SELECT body FROM code");
        //$stmt->bind_param("ii", $offset, $rows_per_page);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;

    }

    public function get_data_code($code)
    {
        $stmt = $this->conn->prepare("SELECT id_code, type, days FROM code WHERE body = ?");
        $stmt->bind_param("s", $code);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($id_code, $type, $days);
            $stmt->fetch();
            $res["id_code"] = $id_code;
            $res["type"] = $type;
            $res["days"] = $days;

            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    public function get_data_second_step($id_code)
    {
        $stmt = $this->conn->prepare("SELECT days FROM code WHERE id_code = ?");
        $stmt->bind_param("i", $id_code);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($days);
            $stmt->fetch();
            $res["days"] = $days;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    public function get_data_prem($id_art)
    {
        $stmt = $this->conn->prepare("SELECT date_begin, date_end, cant_days FROM prem WHERE  id_art = ? ");
        $stmt->bind_param("i", $id_art);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($date_begin, $date_end, $cant_days);
            $stmt->fetch();
            $res["date_begin"] = $date_begin;
            $res["date_end"] = $date_end;
            $res["cant_days"] = $cant_days;

            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    public function get_data_prem_for_bss($id_art)
    {
        $stmt = $this->conn->prepare("SELECT date_begin, date_end, cant_days, buy FROM prem WHERE  id_art = ? ");
        $stmt->bind_param("i", $id_art);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($date_begin, $date_end, $cant_days, $buy);
            $stmt->fetch();
            $res["date_begin"] = $date_begin;
            $res["date_end"] = $date_end;
            $res["cant_days"] = $cant_days;
            $res["buy"] = $buy;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    //get arts for port
    public function get_prem_for_port($id_prov, $is_prem, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE  id_prov = ? AND is_prem = ? ORDER BY prem_prior ASC, created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iiii", $id_prov, $is_prem, $offset, $rows_per_page);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;
    }

    //get premium for bss order by cats
    public function get_prem_for_bss($id_prov, $id_dep, $is_prem, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE  id_prov = ? AND id_dep = ? AND is_prem = ? ORDER BY id_cat ASC, prem_prior ASC, created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iiiii", $id_prov, $id_dep, $is_prem, $offset, $rows_per_page);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;
    }

    public function get_time_is_sended($code)
    {
        $stmt = $this->conn->prepare("SELECT time_sended FROM code WHERE body = ?");
        $stmt->bind_param("s", $code);
        if ($stmt->execute()) {
            $stmt->bind_result($time);
            $stmt->fetch();
            $stmt->close();
            return $time;
        } else {
            return NULL;
        }
    }

    //get datos en tabla premium para mis anuncios premium
    public function get_prem_for_my_arts_prem($id_user, $buy, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM prem WHERE id_user = ? AND  buy = ? ORDER BY date_begin DESC LIMIT ? , ?");
        $stmt->bind_param("iiii", $id_user, $buy, $offset, $rows_per_page);
        $stmt->execute();
        $ids_art = $stmt->get_result();
        $stmt->close();
        return $ids_art;
    }

    //get anuncios mis anuncios top
    public function get_top_for_my_arts_top($id_user, $buy, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM top WHERE id_user = ? AND  buy = ? LIMIT ? , ?");
        $stmt->bind_param("iiii", $id_user, $buy, $offset, $rows_per_page);
        $stmt->execute();
        $ids_art = $stmt->get_result();
        $stmt->close();
        return $ids_art;
    }


    //get art por id para bss get reps
    public function get_art_by_id_for_reps_bss($id)
    {
        $stmt = $this->conn->prepare("SELECT id_art,created_at,img_p,title,price,body,des,vis,coments,coin,is_prem,is_top,id_dep,id_cat,id_prov,id_user FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($id_art, $created_at, $img_p, $title, $price, $body, $des, $vis, $coments, $coin,
                $is_prem, $is_top, $id_dep, $id_cat, $id_prov, $id_user);
            $stmt->fetch();
            $res["id_art"] = $id_art;
            $res["img_p"] = $img_p;
            $res["created_at"] = $created_at;
            $res["title"] = $title;
            $res["price"] = $price;
            $res["body"] = $body;
            $res["des"] = $des;
            $res["vis"] = $vis;
            $res["coments"] = $coments;
            $res["coin"] = $coin;
            $res["is_prem"] = $is_prem;
            $res["is_top"] = $is_top;
            $res["id_dep"] = $id_dep;
            $res["id_cat"] = $id_cat;
            $res["id_prov"] = $id_prov;
            $res["id_user"] = $id_user;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    //get art por id general para varios endpoints
    public function get_art_by_id($id)
    {
        $stmt = $this->conn->prepare("SELECT id_art,created_at,img_p,title,price,body,des,vis,coments,prior,coin,id_dep,id_cat,id_prov,id_user FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($id_art, $created_at, $img_p, $title, $price, $body, $des, $vis, $coments, $prior, $coin, $id_dep, $id_cat, $id_prov, $id_user);
            $stmt->fetch();
            $res["id_art"] = $id_art;
            $res["img_p"] = $img_p;
            $res["created_at"] = $created_at;
            $res["title"] = $title;
            $res["price"] = $price;
            $res["body"] = $body;
            $res["des"] = $des;
            $res["vis"] = $vis;
            $res["coments"] = $coments;
            $res["prior"] = $prior;
            $res["coin"] = $coin;
            $res["id_dep"] = $id_dep;
            $res["id_cat"] = $id_cat;
            $res["id_prov"] = $id_prov;
            $res["id_user"] = $id_user;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    //get ids por provincias para slide
    public function get_ids_arts_by_slide_by_prov($id_prov)
    {
        $stmt = $this->conn->prepare("SELECT id_art FROM slide WHERE ( id_prov = ? )");
        $stmt->bind_param("i", $id_prov);
        $stmt->execute();
        $ids_art = $stmt->get_result();
        $stmt->close();
        return $ids_art;
    }

    //get anuncio para slide
    public function get_art_by_id_for_port_by_prov($id)
    {
        $stmt = $this->conn->prepare("SELECT id_art , created_at , price , title , body , img_p , dep , cat , des , vis , coments , share , prior , id_prov , coin , id_user FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($id_art, $created_at, $price, $title, $body, $img_p, $dep, $cat, $des, $vis, $coments, $share, $prior, $id_prov, $coin, $id_user);
            $stmt->fetch();
            $res["id_art"] = $id_art;
            $res["created_at"] = $created_at;
            $res["price"] = $price;
            $res["title"] = $title;
            $res["body"] = $body;
            $res["img_p"] = $img_p;
            $res["dep"] = $dep;
            $res["cat"] = $cat;
            $res["des"] = $des;
            $res["vis"] = $vis;
            $res["coments"] = $coments;
            $res["share"] = $share;
            $res["prior"] = $prior;
            $res["coin"] = $coin;
            $res["id_prov"] = $id_prov;
            $res["id_user"] = $id_user;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    //obtengo los ids de los premium para boss
    public function get_prem_by_prov_for_boss($id)
    {
        $stmt = $this->conn->prepare("SELECT id_art , created_at , img_p , title , price , body , dep , cat , prov , coin , id_user FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        if ($result != NULL) {
            $res = array();
            $stmt->bind_result($id_art, $created_at, $img_p, $title, $price, $body, $dep, $cat, $prov, $coin, $id_user);
            $stmt->fetch();
            $res["id_art"] = $id_art;
            $res["created_at"] = $created_at;
            $res["img_p"] = $img_p;
            $res["title"] = $title;
            $res["price"] = $price;
            $res["body"] = $body;
            $res["dep"] = $dep;
            $res["cat"] = $cat;
            $res["prov"] = $prov;
            $res["coin"] = $coin;
            $res["id_user"] = $id_user;

            $stmt->close();

            return $res;
        } else {
            return NULL;
        }
    }

    //funcion principal para los listados
    public function get_cat($id_dep, $id_cat, $id_prov, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND id_cat = ? AND id_prov = ?)
        ORDER BY is_top DESC, is_prem DESC , prior ASC, created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iiiii", $id_dep, $id_cat, $id_prov, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    //get ids user des para vista art
    public function get_ids_users_for_des_by_id_art($id_art)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM art_des WHERE id_art = ? ORDER BY created_at DESC LIMIT 35");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $users = $stmt->get_result();
        $stmt->close();
        return $users;
    }

    //get valores user para user des en vista art
    public function get_two_values_user_by_id($id)
    {
        $stmt = $this->conn->prepare("SELECT img, name FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($img, $name);
            $stmt->fetch();
            $user = array();
            $user["img"] = $img;
            $user["name"] = $name;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //obtengo los ids de los anuncios deseados
    public function get_ids_anuncios_deseados_by_id_user($id_user, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT id_art, created_at FROM art_des WHERE id_user = ? ORDER BY created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iii", $id_user, $offset, $rows_per_page);
        $stmt->execute();
        $users = $stmt->get_result();
        $stmt->close();
        return $users;
    }

    //get name user para comenta vista art
    public function get_name_user_for_coment($id_user)
    {
        $stmt = $this->conn->prepare("SELECT name FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($name);
            $stmt->fetch();
            $stmt->close();
            return $name;
        } else {
            return NULL;
        }
    }

    //get imagen de perfil para user coment vista art
    public function get_img_perfil_for_coment($id_user)
    {
        $stmt = $this->conn->prepare("SELECT img FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($img_perfil);
            $stmt->fetch();
            $stmt->close();
            return $img_perfil;
        } else {
            return NULL;
        }
    }

    //get comentarios para vista art
    public function get_coments_by_id_anuncio($id_art)
    {
        $stmt = $this->conn->prepare("SELECT * FROM coment WHERE id_art = ? ORDER BY created_at DESC ");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $comentarios = $stmt->get_result();
        $stmt->close();
        return $comentarios;
    }

    //get imagen del anuncio para eliminar en el server
    public function get_img_p_by_id_art($id_art)
    {
        $stmt = $this->conn->prepare("SELECT img_p FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        if ($stmt->execute()) {
            $stmt->bind_result($img_p);
            $stmt->fetch();
            $stmt->close();
            return $img_p;
        } else {
            return NULL;
        }
    }

    //get imagen del anuncio para eliminar en el server
    public function get_get_password_for_verif($id_user)
    {
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        if ($stmt->execute()) {
            $stmt->bind_result($pass);
            $stmt->fetch();
            $stmt->close();
            return $pass;
        } else {
            return NULL;
        }
    }

    //get user que publica para reportes bss
    public function get_user_by_id_for_rep_bss($id)
    {
        $stmt = $this->conn->prepare("SELECT id_user,img, name, mov,email,id_prov,cant_art FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id_user, $img, $name, $mov, $email, $id_prov, $cant);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["img"] = $img;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["id_prov"] = $id_prov;
            $user["cant_art"] = $cant;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //get user por id para varios endpoinst
    public function get_user_by_id($id)
    {
        $stmt = $this->conn->prepare("SELECT id_user,img, img_port, name, mov,email,id_prov,cant_art FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id_user, $img_perfil, $img_port, $name, $mov, $email, $id_prov, $cant);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["img"] = $img_perfil;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["id_prov"] = $id_prov;
            $user["cant_art"] = $cant;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //get datos de usuario para luego de editar profile
    public function get_user_by_id_for_edit($id)
    {
        $stmt = $this->conn->prepare("SELECT name, mov, email, id_prov FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($name, $mov, $email, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["id_prov"] = $id_prov;

            $stmt->close();

            return $user;
        } else {
            return NULL;
        }
    }

    //para devolver los datos del usuario luego de crear cuenta despues de facebook
    public function get_user_by_id_para_devolver_register($id)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }


    //para devolver los datos del usuario luego de crear cuenta despues de facebook
    public function get_user_by_FB_id_para_devolver_register($id)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user           WHERE fb_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //para devolver los datos del usuario luego de crear cuenta despues de facebook
    public function get_user_by_email_id_para_devolver_register($email)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user           WHERE email = ?");
        $stmt->bind_param("i", $email);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //devolver datos user login
    public function get_user_by_id_para_devolver_login_mov($mov)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user WHERE mov = ?");
        $stmt->bind_param("s", $mov);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //devolver datos user login con facebook
    public function get_user_by_id_para_devolver_login_fb($fb_id)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user 
        WHERE fb_id = ?");
        $stmt->bind_param("s", $fb_id);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //devolver datos depues de login con email
    public function get_user_by_id_para_devolver_login_email($email)

    {

        $stmt = $this->conn->prepare("SELECT id_user,api_key, img, img_port, name, mov, email, sex, id_prov FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->bind_result($id_user, $api_key, $img, $img_port, $name, $mov, $email1, $sex, $id_prov);
            $stmt->fetch();
            $user = array();
            $user["id_user"] = $id_user;
            $user["api_key"] = $api_key;
            $user["img"] = $img;
            $user["img_port"] = $img_port;
            $user["name"] = $name;
            $user["mov"] = $mov;
            $user["email"] = $email1;
            $user["sex"] = $sex;
            $user["id_prov"] = $id_prov;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    //get anunciospudiera interesarte para vista anuncio
    public function get_arts_pud_int_art_for_dep($id_dep, $id_cat, $id_prov, $is_prem)
    {

        //CUENTO SI HAY MINIMO 4 ARTICULOS PREMIUM EN ESTE DEPARTAMENTO

        $stmt1 = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE (id_dep = ? AND id_cat = ?) AND (id_prov = ? AND is_prem = ?)");
        $stmt1->bind_param("iiii", $id_dep, $id_cat, $id_prov, $is_prem);
        if ($stmt1->execute()) {
            $stmt1->bind_result($cant);
            $stmt1->fetch();
            $stmt1->close();

            if ($cant < 3) {

                //NO LLEGAN LOS PREM, CUENTO SI HAY CUATRO DE ESTE DEPARTAMENTO

                $stmt1 = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE (id_dep = ? AND id_cat = ? AND id_prov = ?)");
                $stmt1->bind_param("iii", $id_dep, $id_cat, $id_prov);
                if ($stmt1->execute()) {
                    $stmt1->bind_result($cant);
                    $stmt1->fetch();
                    $stmt1->close();

                    if ($cant < 3) {

                        //NO HAY CUATRO DE ESTE DEPARTAMENTO, DEVUELVO LOS PRIMEROS CUATRO EN DESEOS

                        $stmt = $this->conn->prepare("SELECT * FROM art WHERE id_prov = ? ORDER BY is_prem DESC, is_top DESC , prior ASC, des DESC LIMIT 6");
                        $stmt->bind_param("i", $id_prov);
                        $stmt->execute();
                        $anuncios = $stmt->get_result();
                        $stmt->close();

                    } else {

                        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND id_cat = ? AND id_prov = ?) ORDER BY is_prem DESC, is_top DESC, prior ASC LIMIT 6");
                        $stmt->bind_param("iii", $id_dep, $id_cat, $id_prov);
                        $stmt->execute();
                        $anuncios = $stmt->get_result();
                        $stmt->close();
                    }
                }

            } else {

                $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND id_cat = ?) AND (id_prov = ? AND is_prem = ?) ORDER BY prem_prior, vis DESC LIMIT 6");
                $stmt->bind_param("iiii", $id_dep, $id_cat, $id_prov, $is_prem);
                $stmt->execute();
                $anuncios = $stmt->get_result();
                $stmt->close();

            }
        }

        return $anuncios;
    }

    //get anuncios pudiera interesarte para user contact
    public function get_arts_pud_int_art_for_dep_user_contact($id_dep, $id_prov, $is_prem, $id_user, $id_art)
    {
        $stmt1 = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE (id_dep = ? AND is_prem = ? AND id_prov = ?) AND (id_user != ? AND id_art != ?) ORDER BY vis DESC");
        $stmt1->bind_param("iiiii", $id_dep, $id_prov, $is_prem, $id_user, $id_art);
        if ($stmt1->execute()) {
            $stmt1->bind_result($cant);
            $stmt1->fetch();
            $stmt1->close();

            if ($cant < 3) {

                $stmt1 = $this->conn->prepare("SELECT COUNT(*) FROM art WHERE (id_dep = ? AND id_prov = ?) AND (id_user != ? AND id_art != ?) ORDER BY vis DESC LIMIT 4");
                $stmt1->bind_param("iiii", $id_dep, $id_prov, $id_user, $id_art);
                if ($stmt1->execute()) {
                    $stmt1->bind_result($cant);
                    $stmt1->fetch();
                    $stmt1->close();

                    if ($cant < 3) {

                        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_prov = ? AND  id_user != ?) ORDER BY is_prem DESC, is_top DESC, des DESC LIMIT 4");
                        $stmt->bind_param("ii", $id_prov, $id_user);
                        $stmt->execute();
                        $anuncios = $stmt->get_result();
                        $stmt->close();

                    } else {

                        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND id_prov = ?) AND (id_user != ?) ORDER BY is_prem DESC, is_top DESC, des DESC LIMIT 4");
                        $stmt->bind_param("iii", $id_dep, $id_prov, $id_user);
                        $stmt->execute();
                        $anuncios = $stmt->get_result();
                        $stmt->close();

                    }
                }

            } else {

                $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND is_prem = ? AND id_prov = ?) AND (id_user != ? AND id_art != ?) ORDER BY is_prem DESC, is_top DESC, des DESC LIMIT 4");
                $stmt->bind_param("iiiii", $id_dep, $id_prov, $is_prem, $id_user, $id_art);
                $stmt->execute();
                $anuncios = $stmt->get_result();
                $stmt->close();

            }
        }

        return $anuncios;

    }

    //get anuncios para user contact
    public function get_all_anuncios_user_contact($id_user, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE id_user = ?  LIMIT ? , ?");
        $stmt->bind_param("iii", $id_user, $offset, $rows_per_page);
        $stmt->execute();
        $arts = $stmt->get_result();
        $stmt->close();
        return $arts;
    }

    //get anuncios para mis anuncios publicados
    public function get_all_anuncios_user_for_mis_art_pub($id_user, $is_prem, $offset, $rows_per_page)
    {
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_user = ?) AND (is_prem = ? AND is_top = ?) ORDER BY created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iiiii", $id_user, $is_prem, $is_prem, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    //el anuncio tiene otras imagenes, obtenerlas para eliminarlas
    public function get_paths_by_id_art($id_art)
    {

        $stmt = $this->conn->prepare("SELECT * FROM img WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $rutas = $stmt->get_result();
        $stmt->close();
        return $rutas;
    }

    //get anuncios por categoria con precios para busqueda
    public function get_dep_con_precio_for_page($id_dep, $id_cat, $id_prov, $word, $desde, $hasta, $offset, $rows_per_page)
    {
        $word_aux = "%" . $word . "%";
        //CON AMBOS RANGOS

        if ($desde != "0" && $hasta != "0") {
            $stmt = $this->conn->prepare("SELECT * FROM art WHERE (id_dep = ? AND id_cat = ? AND id_prov = ?) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND ( price >= ? AND price <= ? ) ORDER BY is_prem DESC, is_top DESC, prior ASC LIMIT ?,?");
            $stmt->bind_param("iiissii", $id_dep, $id_cat, $id_prov, $desde, $hasta, $offset, $rows_per_page);
            $stmt->execute();
            $anuncios = $stmt->get_result();
            $stmt->close();
            return $anuncios;
        }
        //A PARTIR DE
        if ($desde != "0" && $hasta == "0") {
            $stmt = $this->conn->prepare("SELECT * from art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND price >= ? ORDER BY is_prem DESC, is_top DESC, prior ASC LIMIT ?,?");
            $stmt->bind_param("iiisii", $id_dep, $id_cat, $id_prov, $desde, $offset, $rows_per_page);
            $stmt->execute();
            $anuncios = $stmt->get_result();
            $stmt->close();
            return $anuncios;
        }

        //HASTA
        if ($desde == "0" && $hasta != "0") {
            $stmt = $this->conn->prepare("SELECT * from art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") AND price <= ? ORDER BY is_prem DESC, is_top DESC, prior ASC LIMIT ?,?");
            $stmt->bind_param("iiisii", $id_dep, $id_cat, $id_prov, $hasta, $offset, $rows_per_page);
            $stmt->execute();
            $anuncios = $stmt->get_result();
            $stmt->close();
            return $anuncios;
        }
    }

    //get  busqueda sin precio por categoris
    public function get_search_no_price_for_page($id_dep, $id_cat, $id_prov, $word, $offset, $rows_per_page)
    {
        $word_aux = "%" . $word . "%";
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE ( id_dep = ? AND id_cat = ? AND id_prov = ?) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") ORDER BY is_prem DESC, is_top DESC, prior ASC LIMIT ? , ?");
        $stmt->bind_param("iiiii", $id_dep, $id_cat, $id_prov, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }

    //get busqueda en portada
    public function get_search_port($id_prov, $word, $offset, $rows_per_page)
    {
        $word_aux = "%" . $word . "%";
        $stmt = $this->conn->prepare("SELECT * FROM art WHERE ( id_prov = ? ) AND ( title LIKE \"$word_aux\" OR body LIKE \"$word_aux\") ORDER BY is_prem DESC, is_top DESC, prior ASC, created_at DESC LIMIT ? , ?");
        $stmt->bind_param("iii", $id_prov, $offset, $rows_per_page);
        $stmt->execute();
        $anuncios = $stmt->get_result();
        $stmt->close();
        return $anuncios;
    }


    //ADD
    public function add_bss_code($code, $type, $days, $id_com)
    {
        //require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        $code_hash = PassHash::hash($code);


        // insert query
        $stmt = $this->conn->prepare("INSERT INTO code (body, type, days, id_com) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("sssi", $code_hash, $type, $days, $id_com);
        $result = $stmt->execute();
        $stmt->close();
        $new_code_id = $this->conn->insert_id;
        if ($result) {
            return $new_code_id;
        } else {
            return NULL;
        }
    }

    public function add_prem_user($date_begin, $date_end, $cant_days, $buy, $id_art, $id_user)
    {
        $stmt = $this->conn->prepare("INSERT INTO prem( date_begin , date_end, cant_days , buy ,id_art, id_user) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssiiii", $date_begin, $date_end, $cant_days, $buy, $id_art, $id_user);
        $result = $stmt->execute();
        $stmt->close();
        $new_prem_id = $this->conn->insert_id;
        if ($result) {
            return $new_prem_id;
        } else {
            return NULL;
        }
    }

    public function add_top_user($date_begin, $date_end, $cant_days, $buy, $id_art, $id_user)
    {
        $stmt = $this->conn->prepare("INSERT INTO top( date_begin , date_end, cant_days , buy, id_art, id_user) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssiiii", $date_begin, $date_end, $cant_days, $buy, $id_art, $id_user);
        $result = $stmt->execute();
        $stmt->close();
        $new_prem_id = $this->conn->insert_id;
        if ($result) {
            return $new_prem_id;
        } else {
            return NULL;
        }
    }


    //registrar user con email and mov facebook
    public function add_user_fb_mov_email($fb_id, $img, $name, $mov, $email, $sex, $id_prov)
    {
        //require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        //$password_hash = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user (fb_id, api_key, img, name, mov, email, sex, id_prov) VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssi", $fb_id, $api_key, $img, $name, $mov, $email, $sex, $id_prov);
        $result = $stmt->execute();
        $stmt->close();
        $new_user_id = $this->conn->insert_id;

        if ($result) {
            return $new_user_id;

        } else {

            return NULL;

        }
    }

    //registrar user con emailfacebook
    public function add_user_fb_email($fb_id, $img, $name, $email, $sex, $id_prov)
    {
        //require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        //$password_hash = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user (fb_id, api_key, img, name, email, sex, id_prov) VALUES
        (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $fb_id, $api_key, $img, $name, $email, $sex, $id_prov);
        $result = $stmt->execute();
        $stmt->close();
        $new_user_id = $this->conn->insert_id;

        if ($result) {
            return $new_user_id;

        } else {

            return NULL;

        }
    }

    //registrar user con  mov facebook
    public function add_user_fb_mov($fb_id, $img, $name, $mov, $sex, $id_prov)
    {
        //require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        //$password_hash = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user (fb_id, api_key, img, name, mov, sex, id_prov) VALUES
        (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $fb_id, $api_key, $img, $name, $mov, $sex, $id_prov);
        $result = $stmt->execute();
        $stmt->close();
        $new_user_id = $this->conn->insert_id;

        if ($result) {
            return $new_user_id;

        } else {

            return NULL;

        }
    }

    //registrar user con email
    public function add_user_email($img, $name, $email, $password, $sex, $id_prov)
    {
        require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        $password_hash = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user (api_key, img, name, email, password_hash, sex, id_prov) VALUES (?, ?, ?, ?, ?, ?,?)");
        $stmt->bind_param("ssssssi", $api_key, $img, $name, $email, $password_hash, $sex, $id_prov);
        $result = $stmt->execute();
        $stmt->close();

        $new_user_id = $this->conn->insert_id;

        if ($result) {
            return $new_user_id;

        } else {

            return NULL;

        }
    }

    //adicionar pass a user cuando se loguea con facebook
    public function add_new_pass($password, $id_user)
    {
        require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        $password_hash = PassHash::hash($password);

        // Generating API key
        //$api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("UPDATE user SET password_hash = ? WHERE id_user = ?");
        $stmt->bind_param("si", $password_hash, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //register user with mov
    public function add_user_mov($img, $name, $mov, $password, $sex, $id_prov)
    {
        require_once 'PassHash.php';


        // First check if user already existed in db
        // Generating password hash
        $password_hash = PassHash::hash($password);

        // Generating API key
        $api_key = $this->generate_api_key();

        // insert query
        $stmt = $this->conn->prepare("INSERT INTO user (api_key, img, name, mov, password_hash, sex, id_prov) VALUES(?, ?, ?, ?, ?, ?,?)");
        $stmt->bind_param("ssssssi", $api_key, $img, $name, $mov, $password_hash, $sex, $id_prov);
        $result = $stmt->execute();
        $stmt->close();
        $new_user_id = $this->conn->insert_id;
        if ($result) {
            return $new_user_id;
        } else {
            return NULL;
        }
    }

    //adicionar anuncio
    public function add_art($img_p, $title, $price, $body, $coin, $id_dep, $id_cat, $id_prov, $id_user)
    {
        $stmt = $this->conn->prepare("INSERT INTO art(img_p, title, price, body, coin, id_dep, id_cat, id_prov, id_user) VALUES(?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssiiii", $img_p, $title, $price, $body, $coin, $id_dep, $id_cat, $id_prov, $id_user);
        $result = $stmt->execute();
        $stmt->close();
        $new_anuncio_id = $this->conn->insert_id;
        if ($result) {
            return $new_anuncio_id;
        } else {
            return NULL;
        }
    }

    //adicionar comentario
    public function add_coment($body, $id_user, $id_art)
    {

        $stmt = $this->conn->prepare("INSERT INTO coment ( body, id_user, id_art) VALUES (?,?,?)");
        $stmt->bind_param("sii", $body, $id_user, $id_art);
        $result = $stmt->execute();
        $stmt->close();
        $new_coment_id = $this->conn->insert_id;

        if ($result) {
            return $new_coment_id;
        } else {
            return NULL;
        }
    }

    //adicionar sugerencia
    public function add_sugest($body, $type, $so, $device, $email, $id_user)
    {

        $stmt = $this->conn->prepare("INSERT INTO feed (body, type, so, device, email,  id_user) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sisssi", $body, $type, $so, $device, $email, $id_user);
        $result = $stmt->execute();
        $stmt->close();
        $new_sugest_id = $this->conn->insert_id;

        if ($result) {
            return $new_sugest_id;
        } else {
            return NULL;
        }
    }

    public function add_img($path, $id_art)
    {

        $stmt = $this->conn->prepare("INSERT INTO img (path, id_art) VALUES (?,?)");
        $stmt->bind_param("si", $path, $id_art);
        $result = $stmt->execute();
        $stmt->close();
        $new_foto_id = $this->conn->insert_id;
        if ($result) {
            return $new_foto_id;
        } else {
            return NULL;
        }
    }

    public function add_rep($tipo_reporte, $id_user, $id_art)
    {
        $stmt = $this->conn->prepare("INSERT INTO rep(type, id_user, id_art) VALUES(?,?,?)");
        $stmt->bind_param("iii", $tipo_reporte, $id_user, $id_art);
        $result = $stmt->execute();
        $stmt->close();
        $new_reporte_id = $this->conn->insert_id;

        if ($result) {
            return $new_reporte_id;
        } else {
            return NULL;
        }
    }

    //adicionar visita en anuncio para vista art
    public function add_visita($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET vis = vis + 1 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //adicionar cant anuncios user luego de subir anuncio
    public function add_cant_arts_user($id_user)
    {
        $stmt = $this->conn->prepare("UPDATE user SET cant_art = cant_art + 1 WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //adicionar deseo a las estadisticas del anuncio
    public function add_deseo_est($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET des = des + 1 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function add_coment_est($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET coments = coments + 1 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //adicionar deseo a la lista
    public function add_deseo_a_lista($id_user, $id_art)
    {
        $stmt = $this->conn->prepare("INSERT INTO art_des( id_user ,id_art) VALUES(?,?)");
        $stmt->bind_param("ii", $id_user, $id_art);
        $result = $stmt->execute();
        $stmt->close();
        $new_lista_deseo_id = $this->conn->insert_id;

        if ($result) {
            return $new_lista_deseo_id;
        } else {
            return NULL;
        }
    }


    //UPDATE

    public function update_prior($id_art, $prior)
    {
        $stmt = $this->conn->prepare("UPDATE art SET prior = ? WHERE id_art = ?");
        $stmt->bind_param("ii", $prior, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function update_premium_prior($id_art, $prior)
    {
        $stmt = $this->conn->prepare("UPDATE art SET prem_prior = ? WHERE id_art = ?");
        $stmt->bind_param("ii", $prior, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function update_datos_venta_premium($used, $mac, $sold, $id_art, $id_user, $id_code)
    {
        $stmt = $this->conn->prepare("UPDATE code SET used = ?, mac_imei_used = ?, sold = ?, 
 id_art = ?, id_user = ?, time_used = NOW() WHERE id_code = ?");
        $stmt->bind_param("isiiii", $used, $mac, $sold, $id_art, $id_user, $id_code);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_datos_venta_top($used, $mac, $sold, $id_art, $id_user, $id_code)
    {
        $stmt = $this->conn->prepare("UPDATE code SET used = ?, mac_imei_used = ?, sold = ?, 
 id_art = ?, id_user = ?, time_used = NOW() WHERE id_code = ?");
        $stmt->bind_param("isiiii", $used, $mac, $sold, $id_art, $id_user, $id_code);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_sended_code($sended, $code)
    {
        $stmt = $this->conn->prepare("UPDATE code SET sended = ? WHERE body = ?");
        $stmt->bind_param("is", $sended, $code);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_is_prem_art($is_prem, $id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET is_prem = ? WHERE id_art = ?");
        $stmt->bind_param("ii", $is_prem, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_is_top_art($is_top, $id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET is_top = ? WHERE id_art = ?");
        $stmt->bind_param("ii", $is_top, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_timestap_sended_code($code)
    {
        $stmt = $this->conn->prepare("UPDATE code SET time_sended = NOW() WHERE body = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_mac_sended($mac, $code)
    {
        $stmt = $this->conn->prepare("UPDATE code SET mac_imei_sended = ? WHERE body = ?");
        $stmt->bind_param("ss", $mac, $code);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //actualizar cantiedad de comentarios en el anuncio
    public function update_cant_coments($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET coments = coments - 1 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar anuncio sin la imagen principal
    public function update_art($id_art, $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov)
    {
        $stmt = $this->conn->prepare("UPDATE art SET coin = ? , price = ? , title = ? , body = ? , id_dep = ?, id_cat = ? , id_prov = ? WHERE id_art = ?");
        $stmt->bind_param("ssssiiii", $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar anuncio en prem/top con imagen principal
    public function update_art_with_img($id_art, $img, $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov)
    {
        $stmt = $this->conn->prepare("UPDATE art SET img_p = ? , coin = ? , price = ? , title = ? , body = ? , id_dep = ?, id_cat = ? , id_prov = ? WHERE id_art = ?");
        $stmt->bind_param("sssssiiii", $img, $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar imagen user luego de loguearse
    public function update_img_user($id_user, $img)
    {
        $stmt = $this->conn->prepare("UPDATE user SET img = ? WHERE id_user = ?");
        $stmt->bind_param("si", $img, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //actualizar email user por fb_id
    public function update_email_useR_by_fb_id($fb_id, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET email = ? WHERE fb_id = ?");
        $stmt->bind_param("ss", $email, $fb_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //actualizar valor updated at en usuario
    public function update_updated_at_user($id_user)
    {
        $stmt = $this->conn->prepare("UPDATE user SET updated_at = CURRENT_TIMESTAMP WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_movil_perfil($id_user, $movil)
    {
        $stmt = $this->conn->prepare("UPDATE user SET mov = ? WHERE id_user = ?");
        $stmt->bind_param("si", $movil, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_prior_art($id_art, $prior)
    {
        $stmt = $this->conn->prepare("UPDATE art SET prior = ? WHERE id_art = ?");
        $stmt->bind_param("ii", $prior, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_nombre_perfil($id_user, $name)
    {
        $stmt = $this->conn->prepare("UPDATE user SET name = ? WHERE id_user = ?");
        $stmt->bind_param("si", $name, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_img_p_by_id_art($path, $id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET img_p = ? WHERE id_art = ?");
        $stmt->bind_param("si", $path, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_correo_perfil($id_user, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET email = ? WHERE id_user = ?");
        $stmt->bind_param("si", $email, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //cambiar password
    public function update_password($id_user, $old_pass, $new_pass)
    {
        require_once 'PassHash.php';

        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE id_user = ?");

        $stmt->bind_param("s", $id_user);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $old_pass)) {
                // User password is correct
                $password_hash_new = PassHash::hash($new_pass);
                $stmt1 = $this->conn->prepare("UPDATE user SET password_hash = ? ");
                $stmt1->bind_param("s", $password_hash_new);

                $stmt1->execute();
                $num_affected_rows = $stmt1->affected_rows;
                $stmt1->close();

                return $num_affected_rows > 0;

            }

        } else {

            return FALSE;

        }

    }

    public function update_sexo($id_user, $sexo)
    {
        $stmt = $this->conn->prepare("UPDATE user SET sex = ? WHERE id_user = ?");
        $stmt->bind_param("si", $sexo, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function update_imagen($ruta, $posc, $id_art)
    {
        $stmt = $this->conn->prepare("UPDATE img SET ruta = ? WHERE id_art = ? AND posc = ?");
        $stmt->bind_param("sis", $ruta, $id_art, $posc);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //update imagen de portada user
    public function update_img_portada($path, $id_user)
    {
        $stmt = $this->conn->prepare("UPDATE user SET img_port = ? WHERE id_user = ?");
        $stmt->bind_param("si", $path, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    public function update_title_to_lowwer_case($id_art, $title)
    {
        $stmt = $this->conn->prepare("UPDATE art SET title = ? WHERE id_art = ?");
        $stmt->bind_param("si", $title, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //actualizar delete prem en art
    public function update_delete_prem_en_art($is_prem, $prem_prior, $id_art, $id_user)
    {
        $stmt = $this->conn->prepare("UPDATE art SET is_prem = ? , prem_prior = ? WHERE id_art = ? AND id_user = ?");
        $stmt->bind_param("iiii", $is_prem, $prem_prior, $id_art, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar delete top en art
    public function update_delete_top_en_art($is_top, $prior, $id_art, $id_user)
    {
        $stmt = $this->conn->prepare("UPDATE art SET is_top = ? , prior = ? WHERE id_art = ? AND id_user = ?");
        $stmt->bind_param("iiii", $is_top, $prior, $id_art, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar eliminar premium en tabla art
    public function update_profile($id_user, $name, $email, $mov, $id_prov)
    {
        $stmt = $this->conn->prepare("UPDATE user SET name = ? , email = ? , mov = ? , id_prov = ? WHERE id_user = ?");
        $stmt->bind_param("sssii", $name, $email, $mov, $id_prov, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //actualizar name
    public function update_name($id_user, $name)
    {
        $stmt = $this->conn->prepare("UPDATE user SET name = ? WHERE id_user = ?");
        $stmt->bind_param("si", $name, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }

    //actualizar email
    public function update_email($id_user, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET email = ? WHERE id_user = ?");
        $stmt->bind_param("si", $email, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }

    //actualizar mov
    public function update_mov($id_user, $mov)
    {
        $stmt = $this->conn->prepare("UPDATE user SET mov = ? WHERE id_user = ?");
        $stmt->bind_param("si", $mov, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }

    //actualizar mov
    public function update_id_prov($id_user, $id_prov)
    {
        $stmt = $this->conn->prepare("UPDATE user SET id_prov = ? WHERE id_user = ?");
        $stmt->bind_param("ii", $id_prov, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows;
    }


    //despues de eliminar el anuncio, restar el contador del usuario
    public function update_cant_arts_user($id_user)
    {
        $stmt = $this->conn->prepare("UPDATE user SET cant_art = cant_art - 1 WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //update cant coments
    public function update_cant_coments_art($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET coments = 0 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    //update rutas imagenes
    public function update_path($id_art, $new_path, $old_path)
    {
        $stmt = $this->conn->prepare("UPDATE img SET path = ? WHERE id_art = ? AND path = ?");
        $stmt->bind_param("sis", $new_path, $id_art, $old_path);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    //update profile facebook
    public function update_profile_to_fb_with_email($fb_id, $name, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET fb_id = ?, name = ? WHERE email = ?");
        $stmt->bind_param("sss", $fb_id, $name, $email);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //update profile facebook
    public function update_profile_to_fb_with_mov($fb_id, $name, $mov, $email)
    {
        $stmt = $this->conn->prepare("UPDATE user SET fb_id = ?, name = ?, email = ? WHERE mov = ?");
        $stmt->bind_param("ssss", $fb_id, $name, $email, $mov);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //update profile facebook
    public function update_profile_to_fb_with_mov1($fb_id, $name, $mov)
    {
        $stmt = $this->conn->prepare("UPDATE user SET fb_id = ?, name = ? WHERE mov = ?");
        $stmt->bind_param("sss", $fb_id, $name, $mov);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //update profile facebook
    public function update_profile_to_fb_with_email_and_mov($fb_id, $name, $email, $mov)
    {
        $stmt = $this->conn->prepare("UPDATE user SET fb_id = ?, name = ?, email = ? WHERE mov = ?");
        $stmt->bind_param("ssss", $fb_id, $name, $email, $mov);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //bss update dep cat

    public function update_dep_cat_by_id_art($id_dep, $id_cat, $id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET id_dep = ?, id_cat = ? WHERE id_art = ?");
        $stmt->bind_param("iii", $id_dep, $id_cat, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;

    }

    //CHECKS

    public function check_is_sended($code, $sended)
    {
        $stmt = $this->conn->prepare("SELECT id_code FROM code WHERE body = ? AND sended = ?");
        $stmt->bind_param("si", $code, $sended);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_exist_code($code)
    {

        /*if (PassHash::check_password($password_hash, $password)) {

        }*/
        //$code_hash = PassHash::hash($code);

        //$full_salt = substr($hash, 0, 29);
        //$new_hash = crypt($password, $full_salt);


        $stmt = $this->conn->prepare("SELECT id_code FROM code WHERE body = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;

    }

    public function check_is_used($code, $used)
    {


        //$code_hash = PassHash::hash($code);
        $stmt = $this->conn->prepare("SELECT id_code FROM code WHERE body = ? AND used = ?");
        $stmt->bind_param("si", $code, $used);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_code_first_step($id_code, $sended, $used)
    {
        $stmt = $this->conn->prepare("SELECT id_code FROM code WHERE ( id_code = ? AND sended = ? AND used = ? )");
        $stmt->bind_param("iii", $id_code, $sended, $used);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_login_email($email, $password)
    {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    public function check_login_mov($mov, $password)
    {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE mov = ?");

        $stmt->bind_param("s", $mov);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }

        } else {
            $stmt->close();

            return FALSE;

        }
    }

    //verificar movil para actualizar profile
    public function check_email_para_edit($email, $id_user)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
        $stmt->bind_param("si", $email, $id_user);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    public function check_nombre_usuario($name)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //chequear si el anuncio tiene otras imagenes
    public function check_imgs_art($id_art)
    {
        $stmt = $this->conn->prepare("SELECT id_img FROM img WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_imagen_en_pos($pos, $id_art)
    {
        $stmt = $this->conn->prepare("SELECT id_img FROM img WHERE posc = ? AND id_art = ?");
        $stmt->bind_param("si", $pos, $id_art);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;

    }

    //verificar si ya fue deseado para vista art
    public function check_deseo($id_user, $id_art)
    {
        $stmt = $this->conn->prepare("SELECT id_des FROM art_des WHERE (id_user = ? AND id_art = ?) ");
        $stmt->bind_param("ii", $id_user, $id_art);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_seguir_user($from_user, $to_user)
    {
        $stmt = $this->conn->prepare("SELECT * FROM follow WHERE (from_user = ? AND to_user = ?) ");
        $stmt->bind_param("ii", $from_user, $to_user);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_rep($id_user, $id_art)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rep WHERE (id_user = ? AND id_art = ?) ");
        $stmt->bind_param("ii", $id_user, $id_art);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //verificar correo del usuario
    public function check_email_usuario($email)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //verificar movil del usuario
    public function check_mov_usuario($mov)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE mov = ?");
        $stmt->bind_param("s", $mov);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //verificar fb_id del usuario
    public function check_fb_id_user($fb_id)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE fb_id = ?");
        $stmt->bind_param("s", $fb_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //verificar fb_id del usuario
    public function check_email_by_fb_id($fb_id, $email)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE fb_id = ? AND email = ?");
        $stmt->bind_param("ss", $fb_id, $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function check_api_key($api_key)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //verificar movil para actualizar profile
    public function check_mov_para_edit($mov, $id_user)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE mov = ? AND id_user != ?");
        $stmt->bind_param("si", $mov, $id_user);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    //DELETE
    public function delete_seguir_user($from_user, $to_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM follow WHERE from_user = ? AND to_user = ?");
        $stmt->bind_param("ii", $from_user, $to_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function delete_deseo_est($id_art)
    {
        $stmt = $this->conn->prepare("UPDATE art SET des = des - 1 WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    public function delete_imagen($id_art, $path)
    {
        $stmt = $this->conn->prepare("DELETE FROM img WHERE id_art = ? AND path = ?");
        $stmt->bind_param("is", $id_art, $path);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //eliminar comentario
    public function delete_coment($id_user, $id_coment)
    {
        $stmt = $this->conn->prepare("DELETE FROM coment WHERE id_user = ? AND id_coment = ?");
        $stmt->bind_param("ii", $id_user, $id_coment);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //despues de editar imagen principal eliminar comentarios
    public function delete_coments_after_edit_img_p($id_art)
    {
        $stmt = $this->conn->prepare("DELETE FROM coment WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function delete_deseo_lista($id_user, $id_art)
    {
        $stmt = $this->conn->prepare("DELETE FROM art_des WHERE id_user = ? AND id_art = ?");
        $stmt->bind_param("ii", $id_user, $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function delete_art_des_by_id_des($id_art_des)
    {
        $stmt = $this->conn->prepare("DELETE FROM art_des WHERE id_des = ?");
        $stmt->bind_param("i", $id_art_des);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //eliminar la lista de deseos del usuario
    public function delete_all_lista_by_user($id_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM art_des WHERE id_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    public function delete_notif_by_user($id_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM notif WHERE to_user = ?");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //eliminar anuncio
    public function delete_art($id_art)
    {
        $stmt = $this->conn->prepare("DELETE FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //delete premium bss
    public function bss_delete_prem($id_art, $id_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM prem WHERE id_art = ? AND id_user = ?");
        $stmt->bind_param("ii", $id_art, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //delete top bss
    public function bss_delete_top($id_art, $id_user)
    {
        $stmt = $this->conn->prepare("DELETE FROM top WHERE id_art = ? AND id_user = ?");
        $stmt->bind_param("ii", $id_art, $id_user);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //delete art bss
    public function bss_delete_art($id_art)
    {
        $stmt = $this->conn->prepare("DELETE FROM art WHERE id_art = ?");
        $stmt->bind_param("i", $id_art);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    //delete rep bss
    public function bss_delete_rep($id_rep)
    {
        $stmt = $this->conn->prepare("DELETE FROM rep WHERE id_rep = ?");
        $stmt->bind_param("i", $id_rep);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    private function generate_api_key()
    {
        return md5(uniqid(rand(), true));
    }

    public function isValidApiKey($api_key)
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

}

