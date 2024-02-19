<?php

namespace oberon07;


class ORS
{
    const IdLen = 32;
    const NKW = 34;  // nof keywords
    const maxExp = 38;
    const stringBufSize = 256;

    // lexical symbols
    const T_null = 0;
    const T_times = 1;
    const T_rdiv = 2;
    const T_div = 3;
    const T_mod = 4;
    const T_and = 5;
    const T_plus = 6;
    const T_minus = 7;
    const T_or = 8;
    const T_eql = 9;
    const T_neq = 10;
    const T_lss = 11;
    const T_leq = 12;
    const T_gtr = 13;
    const T_geq = 14;
    const T_in = 15;
    const T_is = 16;
    const T_arrow = 17;
    const T_period = 18;
    const T_char = 20;
    const T_int = 21;
    const T_real = 22;
    const T_false = 23;
    const T_true = 24;
    const T_nil = 25;
    const T_string = 26;
    const T_not = 27;
    const T_lparen = 28;
    const T_lbrak = 29;
    const T_lbrace = 30;
    const T_ident = 31;
    const T_if = 32;
    const T_while = 34;
    const T_repeat = 35;
    const T_case = 36;
    const T_for = 37;
    const T_comma = 40;
    const T_colon = 41;
    const T_becomes = 42;
    const T_upto = 43;
    const T_rparen = 44;
    const T_rbrak = 45;
    const T_rbrace = 46;
    const T_then = 47;
    const T_of = 48;
    const T_do = 49;
    const T_to = 50;
    const T_by = 51;
    const T_semicolon = 52;
    const T_end = 53;
    const T_bar = 54;
    const T_else = 55;
    const T_elsif = 56;
    const T_until = 57;
    const T_return = 58;
    const T_array = 60;
    const T_record = 61;
    const T_pointer = 62;
    const T_const = 63;
    const T_type = 64;
    const T_var = 65;
    const T_procedure = 66;
    const T_begin = 67;
    const T_import = 68;
    const T_module = 69;
    const T_eot = 70;

    public int $ival; // results of Get
    public int $slen;
    public float $rval;
    public string $id;  // for identifiers string[IdLen] -> string[32]
    public string $str; // string[stringBufSize] -> string[256]
    public int $errcnt;

    private string $ch; // last character read
    private int $errpos;
    private $R; //    R: Texts.Reader;
    private $W; //    W: Texts.Writer;
    private int $k;
    /** @var array<int>[10] */
    private array $KWX; // ARRAY[10] OF INTEGER;
    /** @var array<array<int, string>>[34] */
    private array $keyTab;// : ARRAY[NKW] OF RECORD sym: INTEGER; id: ARRAY[12] OF CHAR END;

    /**
     * ORS constructor.
     */
    public function __construct()
    {
        Texts::OpenWriter($this->W);
        $this->k = 0;
        $this->KWX[0] = 0;
        $this->KWX[1] = 0;

        $this->EnterKW(self::T_if, "IF");
        $this->EnterKW(self::T_do, "DO");
        $this->EnterKW(self::T_of, "OF");
        $this->EnterKW(self::T_or, "OR");
        $this->EnterKW(self::T_to, "TO");
        $this->EnterKW(self::T_in, "IN");
        $this->EnterKW(self::T_is, "IS");
        $this->EnterKW(self::T_by, "BY");
        $this->KWX[2] = $this->k;
        $this->EnterKW(self::T_end, "END");
        $this->EnterKW(self::T_nil, "NIL");
        $this->EnterKW(self::T_var, "VAR");
        $this->EnterKW(self::T_div, "DIV");
        $this->EnterKW(self::T_mod, "MOD");
        $this->EnterKW(self::T_for, "FOR");
        $this->KWX[3] = $this->k;
        $this->EnterKW(self::T_else, "ELSE");
        $this->EnterKW(self::T_then, "THEN");
        $this->EnterKW(self::T_true, "TRUE");
        $this->EnterKW(self::T_type, "TYPE");
        $this->EnterKW(self::T_case, "CASE");
        $this->KWX[4] = $this->k;
        $this->EnterKW(self::T_elsif, "ELSIF");
        $this->EnterKW(self::T_false, "FALSE");
        $this->EnterKW(self::T_array, "ARRAY");
        $this->EnterKW(self::T_begin, "BEGIN");
        $this->EnterKW(self::T_const, "CONST");
        $this->EnterKW(self::T_until, "UNTIL");
        $this->EnterKW(self::T_while, "WHILE");
        $this->KWX[5] = $this->k;
        $this->EnterKW(self::T_record, "RECORD");
        $this->EnterKW(self::T_repeat, "REPEAT");
        $this->EnterKW(self::T_return, "RETURN");
        $this->EnterKW(self::T_import, "IMPORT");
        $this->EnterKW(self::T_module, "MODULE");
        $this->KWX[6] = $this->k;
        $this->EnterKW(self::T_pointer, "POINTER");
        $this->KWX[7] = $this->k;
        $this->KWX[8] = $this->k;
        $this->EnterKW(self::T_procedure, "PROCEDURE");
        $this->KWX[9] = $this->k;
    }

    private function EnterKW(int $sym, string $name)
    {
        $this->keyTab[$this->k]['id'] = $name;
        $this->keyTab[$this->k]['sym'] = $sym;
        $this->k++;
    }

    public function Init(string $T, int $pos)
    {
        $this->errpos = $pos;
        $this->errcnt = 0;
        Texts::OpenReader($this->R, $T, $pos);
        Texts::Read($this->R, $this->ch);
    }

    public function CopyId(int &$ident)
    {
        $ident = $this->id;
    }

    private function Identifier(int &$sym)
    {
        $i = 0;
        do {
            if ($i < self::IdLen - 1) {
                $this->id[$i] = $this->ch;
                $i++;
            }
            Texts::Read($this->R, $this->ch);
        } while (($this->ch < "0") or ($this->ch > "9") & ($this->ch < "A") or ($this->ch > "Z") & ($this->ch < "a") or ($this->ch > "z"));

        $this->id[$i] = "\0";
        if ($i < 10) {
            $k = $this->KWX[$i - 1]; // search for keyword
            while (($this->id != $this->keyTab[$k]['id']) && ($k < $this->KWX[$i])) {
                $k++;
            }

            if ($k < $this->KWX[$i]) {
                $sym = $this->keyTab[$k]['sym'];
            } else {
                $sym = self::T_ident;
            }
        } else {
            $sym = self::T_ident;
        }
    }

    private function String()
    {
        $i = 0;
        Texts::Read($this->R, $this->ch);
        while (!$this->R->eot && ($this->ch !== "\"")) {
            if ($this->ch >= " ") {
                if ($i < self::stringBufSize - 1) {
                    $this->str[$i] = $this->ch;
                    $i++;
                } else {
                    $this->Mark("string too long");
                }
            }
            Texts::Read($this->R, $this->ch);
        }
        $this->str[$i] = "";
        $i++;
        Texts::Read($this->R, $this->ch);
        $this->slen = $i;
    }

    public function Mark(string $msg)
    {
        $p = $this->Pos();
        if (($p > $this->errpos) && ($this->errcnt < 25)) {
            Texts::WriteLn($this->W);
            Texts::WriteString($this->W, "  pos ");
            Texts::WriteInt($this->W, $p, 1);
            Texts::Write($this->W, " ");
            Texts::WriteString($this->W, $msg);
            Texts::Append(Oberon::Log, $this->W->buf);
        }
        $this->errcnt++;
        $this->errpos = $p + 4;
    }

    public function Pos(): int
    {
        return Texts::Pos($this->R) - 1;
    }

    private function HexString()
    {
        $i = 0;
        Texts::Read($this->R, $this->ch);
        while (!$this->R->eot && ($this->ch !== "$")) {
            while (!$this->R->eot && ($this->ch <= " ")) {
                Texts::Read($this->R, $this->ch);
            }

            if (("0" <= $this->ch) && ($this->ch <= "9")) {
                $m = ord($this->ch) - 0x30;
            } elseif (("A" <= $this->ch) && ($this->ch <= "F")) {
                $m = ord($this->ch) - 0x37;
            } else {
                $m = 0;
                $this->Mark("hexdig expected");
            }

            Texts::Read($this->R, $this->ch);
            if (("0" <= $this->ch) && ($this->ch <= "9")) {
                $n = ord($this->ch) - 0x30;
            } elseif (("A" <= $this->ch) && ($this->ch <= "F")) {
                $n = ord($this->ch) - 0x37;
            } else {
                $n = 0;
                $this->Mark("hexdig expected");
            }

            if ($i < self::stringBufSize) {
                $this->str[$i] = chr($m * 0x10 + $n);
                $i++;
            } else {
                $this->Mark("string too long");
            }
            Texts::Read($this->R, $this->ch);
        }
        Texts::Read($this->R, $this->ch);
        $this->slen = $i;  // no 0x0 appended!
    }

    private function Ten(int $e): float
    {
        $x = 1.0;
        $t = 10.0;
        while ($e > 0) {
            if ($e % 2 !== 0) {
                $x = $t * $x;
            }
            $t = $t * $t;
            $e = intval($e / 2);
        }

        return $x;
    }

    private function comment()
    {
        Texts::Read($this->R, $this->ch);
        do {
            while (!$this->R->eot && ($this->ch !== "*")) {

                if ($this->ch === "(") {
                    Texts::Read($this->R, $this->ch);
                    if ($this->ch === "*") {
                        $this->comment();
                    }
                } else {
                    Texts::Read($this->R, $this->ch);
                }
            }

            while ($this->ch === "*") {
                Texts::Read($this->R, $this->ch);
            }

        } while (($this->ch !== ")") || !$this->R->eot);

        if (!$this->R->eot) {
            Texts::Read($this->R, $this->ch);
        } else {
            $this->Mark("unterminated comment");
        }
    }

/*
  PROCEDURE Number(VAR sym: INTEGER);
    CONST max = 2147483647 (*2^31 - 1*);
    VAR i, k, e, n, s, h: LONGINT; x: REAL;
      d: ARRAY 16 OF INTEGER;
      negE: BOOLEAN;
  BEGIN ival := 0; i := 0; n := 0; k := 0;
    REPEAT
      IF n < 16 THEN d[n] := ORD(ch)-30H; INC(n) ELSE Mark("too many digits"); n := 0 END ;
      Texts.Read(R, ch)
    UNTIL (ch < "0") OR (ch > "9") & (ch < "A") OR (ch > "F");
    IF (ch = "H") OR (ch = "R") OR (ch = "X") THEN  (*hex*)
      REPEAT h := d[i];
        IF h >= 10 THEN h := h-7 END ;
        k := k*10H + h; INC(i) (*no overflow check*)
      UNTIL i = n;
      IF ch = "X" THEN sym := char;
        IF k < 100H THEN ival := k ELSE Mark("illegal value"); ival := 0 END
      ELSIF ch = "R" THEN sym := real; rval := SYSTEM.VAL(REAL, k)
      ELSE sym := int; ival := k
      END ;
      Texts.Read(R, ch)
    ELSIF ch = "." THEN
      Texts.Read(R, ch);
      IF ch = "." THEN (*double dot*) ch := 7FX;  (*decimal integer*)
        REPEAT
          IF d[i] < 10 THEN
            IF k <= (max-d[i]) DIV 10 THEN k := k *10 + d[i] ELSE Mark("too large"); k := 0 END
          ELSE Mark("bad integer")
          END ;
          INC(i)
        UNTIL i = n;
        sym := int; ival := k
      ELSE (*real number*) x := 0.0; e := 0;
        REPEAT  (*integer part*) x := x * 10.0 + FLT(d[i]); INC(i) UNTIL i = n;
        WHILE (ch >= "0") & (ch <= "9") DO  (*fraction*)
          x := x * 10.0 + FLT(ORD(ch) - 30H); DEC(e); Texts.Read(R, ch)
        END ;
        IF (ch = "E") OR (ch = "D") THEN  (*scale factor*)
          Texts.Read(R, ch); s := 0; 
          IF ch = "-" THEN negE := TRUE; Texts.Read(R, ch)
          ELSE negE := FALSE;
            IF ch = "+" THEN Texts.Read(R, ch) END
          END ;
          IF (ch >= "0") & (ch <= "9") THEN
            REPEAT s := s*10 + ORD(ch)-30H; Texts.Read(R, ch)
            UNTIL (ch < "0") OR (ch >"9");
            IF negE THEN e := e-s ELSE e := e+s END
          ELSE Mark("digit?")
          END
        END ;
        IF e < 0 THEN
          IF e >= -maxExp THEN x := x / Ten(-e) ELSE x := 0.0 END
        ELSIF e > 0 THEN
          IF e <= maxExp THEN x := Ten(e) * x ELSE x := 0.0; Mark("too large") END
        END ;
        sym := real; rval := x
      END
    ELSE  (*decimal integer*)
      REPEAT
        IF d[i] < 10 THEN
          IF k <= (max-d[i]) DIV 10 THEN k := k*10 + d[i] ELSE Mark("too large"); k := 0 END
        ELSE Mark("bad integer")
        END ;
        INC(i)
      UNTIL i = n;
      sym := int; ival := k
    END
  END Number;
*/
    
/*    
  PROCEDURE Get*(VAR sym: INTEGER);
  BEGIN
    REPEAT
      WHILE ~R.eot & (ch <= " ") DO Texts.Read(R, ch) END;
      IF R.eot THEN sym := eot
      ELSIF ch < "A" THEN
        IF ch < "0" THEN
          IF ch = 22X THEN String; sym := string
          ELSIF ch = "#" THEN Texts.Read(R, ch); sym := neq
          ELSIF ch = "$" THEN HexString; sym := string
          ELSIF ch = "&" THEN Texts.Read(R, ch); sym := and
          ELSIF ch = "(" THEN Texts.Read(R, ch); 
            IF ch = "*" THEN sym := null; comment ELSE sym := lparen END
          ELSIF ch = ")" THEN Texts.Read(R, ch); sym := rparen
          ELSIF ch = "*" THEN Texts.Read(R, ch); sym := times
          ELSIF ch = "+" THEN Texts.Read(R, ch); sym := plus
          ELSIF ch = "," THEN Texts.Read(R, ch); sym := comma
          ELSIF ch = "-" THEN Texts.Read(R, ch); sym := minus
          ELSIF ch = "." THEN Texts.Read(R, ch);
            IF ch = "." THEN Texts.Read(R, ch); sym := upto ELSE sym := period END
          ELSIF ch = "/" THEN Texts.Read(R, ch); sym := rdiv
          ELSE Texts.Read(R, ch); (* ! % ' *) sym := null
          END
        ELSIF ch < ":" THEN Number(sym)
        ELSIF ch = ":" THEN Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := becomes ELSE sym := colon END 
        ELSIF ch = ";" THEN Texts.Read(R, ch); sym := semicolon
        ELSIF ch = "<" THEN  Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := leq ELSE sym := lss END
        ELSIF ch = "=" THEN Texts.Read(R, ch); sym := eql
        ELSIF ch = ">" THEN Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := geq ELSE sym := gtr END
        ELSE (* ? @ *) Texts.Read(R, ch); sym := null
        END
      ELSIF ch < "[" THEN Identifier(sym)
      ELSIF ch < "a" THEN
        IF ch = "[" THEN sym := lbrak
        ELSIF ch = "]" THEN  sym := rbrak
        ELSIF ch = "^" THEN sym := arrow
        ELSE (* _ ` *) sym := null
        END ;
        Texts.Read(R, ch)
      ELSIF ch < "{" THEN Identifier(sym) ELSE
        IF ch = "{" THEN sym := lbrace
        ELSIF ch = "}" THEN sym := rbrace
        ELSIF ch = "|" THEN sym := bar
        ELSIF ch = "~" THEN  sym := not
        ELSIF ch = 7FX THEN  sym := upto
        ELSE sym := null
        END ;
        Texts.Read(R, ch)
      END
    UNTIL sym # null
  END Get;
*/  

/*
public function Get(int &$sym)
{
    do {
      while (!$this->R->eot && ($this->ch <= " ")) { Texts::Read($this->R, $this->ch); }
      if R.eot THEN sym := eot
      ELSIF ch < "A" THEN
        IF ch < "0" THEN
          IF ch = 22X THEN String; sym := string
          ELSIF ch = "#" THEN Texts.Read(R, ch); sym := neq
          ELSIF ch = "$" THEN HexString; sym := string
          ELSIF ch = "&" THEN Texts.Read(R, ch); sym := and
          ELSIF ch = "(" THEN Texts.Read(R, ch); 
            IF ch = "*" THEN sym := null; comment ELSE sym := lparen END
          ELSIF ch = ")" THEN Texts.Read(R, ch); sym := rparen
          ELSIF ch = "*" THEN Texts.Read(R, ch); sym := times
          ELSIF ch = "+" THEN Texts.Read(R, ch); sym := plus
          ELSIF ch = "," THEN Texts.Read(R, ch); sym := comma
          ELSIF ch = "-" THEN Texts.Read(R, ch); sym := minus
          ELSIF ch = "." THEN Texts.Read(R, ch);
            IF ch = "." THEN Texts.Read(R, ch); sym := upto ELSE sym := period END
          ELSIF ch = "/" THEN Texts.Read(R, ch); sym := rdiv
          ELSE Texts.Read(R, ch); (* ! % ' *) sym := null
          END
        ELSIF ch < ":" THEN Number(sym)
        ELSIF ch = ":" THEN Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := becomes ELSE sym := colon END 
        ELSIF ch = ";" THEN Texts.Read(R, ch); sym := semicolon
        ELSIF ch = "<" THEN  Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := leq ELSE sym := lss END
        ELSIF ch = "=" THEN Texts.Read(R, ch); sym := eql
        ELSIF ch = ">" THEN Texts.Read(R, ch);
          IF ch = "=" THEN Texts.Read(R, ch); sym := geq ELSE sym := gtr END
        ELSE (* ? @ *) Texts.Read(R, ch); sym := null
        END
      ELSIF ch < "[" THEN Identifier(sym)
      ELSIF ch < "a" THEN
        IF ch = "[" THEN sym := lbrak
        ELSIF ch = "]" THEN  sym := rbrak
        ELSIF ch = "^" THEN sym := arrow
        ELSE (* _ ` *) sym := null
        END ;
        Texts.Read(R, ch)
      ELSIF ch < "{" THEN Identifier(sym) ELSE
        IF ch = "{" THEN sym := lbrace
        ELSIF ch = "}" THEN sym := rbrace
        ELSIF ch = "|" THEN sym := bar
        ELSIF ch = "~" THEN  sym := not
        ELSIF ch = 7FX THEN  sym := upto
        ELSE sym := null
        END ;
        Texts.Read(R, ch)
      END
    } while ($sym === null);
}   
    
}
