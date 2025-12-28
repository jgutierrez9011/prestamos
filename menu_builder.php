<?php
require_once '../cn.php';


class MenuBuilder {
    private $db;
    private $user;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
    }

    public function buildMenu() {
        $menuData = $this->getMenuData();
        ob_start();
        include 'menu_template.php';
        return ob_get_clean();
    }

    private function getMenuData() {
        $sql = "SELECT COUNT(*) FROM tblcatusuario as a
                INNER JOIN tblcatmenuperfil as b ON a.intidperfil = b.idperfil
                INNER JOIN tblcatmenu as c ON b.intidmenu = c.intidmenu
                WHERE a.strusuario = ? AND b.bolactivo = '1' 
                AND a.bolactivo = '1' AND c.strnivelmenu = '1'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->user]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        
        if ($row[0] <= 0) {
            return ['hasMenu' => false];
        }

        $sql = "SELECT b.idperfil, c.strtipomenu, c.strmenu, c.strhref, c.strclassicono
                FROM tblcatusuario as a
                INNER JOIN tblcatmenuperfil as b ON a.intidperfil = b.idperfil
                INNER JOIN tblcatmenu as c ON b.intidmenu = c.intidmenu
                WHERE a.strusuario = ? AND b.bolactivo = '1' 
                AND a.bolactivo = '1' AND c.strnivelmenu = '1'
                ORDER BY c.intidmenu ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->user]);
        $mainMenuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($mainMenuItems as &$item) {
            $item['submenus'] = $this->getSubmenuData($item['idperfil'], $item['strtipomenu']);
        }

        return [
            'hasMenu' => true,
            'mainMenu' => $mainMenuItems
        ];
    }

    private function getSubmenuData($idperfil, $tipomenu) {
        $sql = "SELECT COUNT(*) FROM tblcatperfilusrfrm as a
                INNER JOIN tblcatformularios as b ON a.idfrm = b.idfrm
                INNER JOIN tblcatperfilusr as c ON a.idperfil = c.idperfil
                WHERE a.idperfil = ? AND b.strkeymenu = ? AND a.bolactivo = '1'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idperfil, $tipomenu]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        
        if ($row[0] <= 0) {
            return [];
        }

        $sql = "SELECT a.idperfilusrfrm, b.strformulario, b.strnombreform, a.bolactivo, c.strperfil, b.strkeymenu
                FROM tblcatperfilusrfrm as a
                INNER JOIN tblcatformularios as b ON a.idfrm = b.idfrm
                INNER JOIN tblcatperfilusr as c ON a.idperfil = c.idperfil
                WHERE a.idperfil = ? AND b.strkeymenu = ? AND a.bolactivo = '1'
                ORDER BY a.idperfilusrfrm ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idperfil, $tipomenu]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>