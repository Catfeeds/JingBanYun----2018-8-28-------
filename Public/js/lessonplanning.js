function WebOpen() {
    try {
        switch (flag) {
            case '0':
                frm.WebOffice.WebOpen(strOpenUrl);
                break;
            case '2':
                frm.WebOffice.CreateNew("Excel.Sheet");
                break;
            case '3':
                frm.WebOffice.CreateNew("PowerPoint.Show");
                break;
            default:
                frm.WebOffice.CreateNew("Word.Document");
        }
    }
    catch (err) {
    }
}

function WebSave() {
    try {

        frm.WebOffice.Save(strURL);

    }
    catch (e) {

    }
    return true;
}
function WebSaveLocal() {
    frm.WebOffice.showdialog(3);
}
function WebOpenLocal() {
    frm.WebOffice.showdialog(1);
}
function WebDocReload() {
    location.reload();
}
function WebOpenPicture() {
    frm.WebOffice.ActiveDocument.Application.Dialogs(163).Show();
}
function WebDocPrint() {
    frm.WebOffice.printout(true);
}
function WebDocPageSetup() {
    frm.WebOffice.showdialog(5);
}
function ShowRevision(boolvalue) {
    try {
        frm.WebOffice.ActiveDocument.ActiveWindow.View.Reviewers(1).Visible = boolvalue;

        frm.WebOffice.ActiveDocument.TrackRevisions = boolvalue;
    }
    catch (E) {
        alert('�ĵ����޺ۼ���');
    }
    //frm.WebOffice.ActiveDocument.ShowComments;
}
function WebDocSignature() {
    try {
        frm.WebOffice.WebSign();

        var test;
        var strFile = frm.WebOffice.WebSignTempFile;

        frm.WebOffice.title = strFile;
        var doc = frm.WebOffice.ActiveDocument;

        doc.Shapes.AddPicture(frm.WebOffice.title, false, true);
        doc.Shapes(doc.Shapes.Count).Select();
        var range = doc.Application.Selection.ShapeRange;
        range.WrapFormat.Type = 3;
        range.PictureFormat.TransparentBackground = true;
        range.PictureFormat.TransparencyColor = 0xFFFFFF;
        range.Fill.Visible = false;
        frm.WebOffice.WebSignTempFileDel();
    }
    catch (E) {

    }
}
function WebTempFile() {
    //����
    var object = frm.WebOffice.ActiveDocument;
    var myl = object.Shapes.AddLine(100, 60, 305, 60)
    myl.Line.ForeColor = 255;
    myl.Line.Weight = 2;
    var myl1 = object.Shapes.AddLine(326, 60, 520, 60)
    myl1.Line.ForeColor = 255;
    myl1.Line.Weight = 2;

    //object.Shapes.AddLine(200,200,450,200).Line.ForeColor=6;
    var myRange = frm.WebOffice.ActiveDocument.Range(0, 0);
    myRange.Select();

    var mtext = "��";
    frm.WebOffice.ActiveDocument.Application.Selection.Range.InsertAfter(mtext + "\n");
    var myRange = frm.WebOffice.ActiveDocument.Paragraphs(1).Range;
    myRange.ParagraphFormat.LineSpacingRule = 1.5;
    myRange.font.ColorIndex = 6;
    myRange.ParagraphFormat.Alignment = 1;
    myRange = frm.WebOffice.ActiveDocument.Range(0, 0);
    myRange.Select();
    mtext = "[��������]��������";
    frm.WebOffice.ActiveDocument.Application.Selection.Range.InsertAfter(mtext + "\n");
    myRange = frm.WebOffice.ActiveDocument.Paragraphs(1).Range;
    myRange.ParagraphFormat.LineSpacingRule = 1.5;
    myRange.ParagraphFormat.Alignment = 1;
    myRange.font.ColorIndex = 1;

    mtext = "���������ļ�";
    frm.WebOffice.ActiveDocument.Application.Selection.Range.InsertAfter(mtext + "\n");
    myRange = frm.WebOffice.ActiveDocument.Paragraphs(1).Range;
    myRange.ParagraphFormat.LineSpacingRule = 1.5;

    //myRange.Select();
    myRange.Font.ColorIndex = 6;
    myRange.Font.Name = "����_GB2312";
    myRange.font.Bold = true;
    myRange.Font.Size = 50;
    myRange.ParagraphFormat.Alignment = 1;

    //myRange=myRange=frm.WebOffice.ActiveDocument.Paragraphs(1).Range;
    frm.WebOffice.ActiveDocument.PageSetup.LeftMargin = 70;
    frm.WebOffice.ActiveDocument.PageSetup.RightMargin = 70;
    frm.WebOffice.ActiveDocument.PageSetup.TopMargin = 70;
    frm.WebOffice.ActiveDocument.PageSetup.BottomMargin = 70;
}
function WebSetWordTable() {
    var mText = "", mTmp = "", iColumns, iCells, iPost, iold = -1;
    var myRange = frm.WebOffice.ActiveDocument.Range(0, 0);     //���λ��

    frm.WebOffice.ActiveDocument.Tables.Add(myRange, 10, 10);   //��ɱ��
    //alert(mText);
    for (var n = 0; n < iColumns; n++) {
        for (var i = 0; i < iCells; i++) {
            iPos = mText.indexOf(";", 1 + iold);
            mTmp = mText.substring(iold + 1, iPos);
            frm.WebOffice.ActiveDocument.Tables(1).Columns(n + 1).Cells(i + 1).Range.Text = mTmp;   //��䵥Ԫֵ
            iold = iPos;
        }
    }


}
function WebGetWordContent() {
    try {
        alert(frm.WebOffice.ActiveDocument.Content.Text);
    } catch (e) {
    }
}
function WebSetWordContent() {
    var mText = window.prompt("����������:", "��������");
    if (mText == null) {
        return (false);
    }
    else {
        //����Ϊ��ʾѡ�е��ı�
        //alert(frm.WebOffice.ActiveDocument.Application.Selection.Range.Text);
        //����Ϊ�ڵ�ǰ���������ı�
        frm.WebOffice.ActiveDocument.Application.Selection.Range.InsertAfter(mText + "\n");
        //����Ϊ�ڵ�һ�κ�����ı�
        //frm.WebOffice.ActiveDocument.Application.ActiveDocument.Range(1).InsertAfter(mText);
    }
}
function WebGetExcelContent() {
    frm.WebOffice.ActiveDocument.Application.Sheets(1).Select;
    frm.WebOffice.ActiveDocument.Application.Range("C5").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "126";
    frm.WebOffice.ActiveDocument.Application.Range("C6").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "446";
    frm.WebOffice.ActiveDocument.Application.Range("C7").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "556";
    frm.WebOffice.ActiveDocument.Application.Range("C5:C8").Select;
    frm.WebOffice.ActiveDocument.Application.Range("C8").Activate;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "=SUM(R[-3]C:R[-1]C)";
    frm.WebOffice.ActiveDocument.Application.Range("D8").Select;
    alert(frm.WebOffice.ActiveDocument.Application.Range("C8").Text);
}
//���ã����������?Ԫ
function WebSheetsLock() {
    frm.WebOffice.ActiveDocument.Application.Sheets(1).Select;

    frm.WebOffice.ActiveDocument.Application.Range("A1").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "��Ʒ";
    frm.WebOffice.ActiveDocument.Application.Range("B1").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "�۸�";
    frm.WebOffice.ActiveDocument.Application.Range("C1").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "��ϸ˵��";
    frm.WebOffice.ActiveDocument.Application.Range("D1").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "���";
    frm.WebOffice.ActiveDocument.Application.Range("A2").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "��ǩ";
    frm.WebOffice.ActiveDocument.Application.Range("A3").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "ë��";
    frm.WebOffice.ActiveDocument.Application.Range("A4").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "�ֱ�";
    frm.WebOffice.ActiveDocument.Application.Range("A5").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "����";

    frm.WebOffice.ActiveDocument.Application.Range("B2").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "0.5";
    frm.WebOffice.ActiveDocument.Application.Range("C2").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "ӣ��";
    frm.WebOffice.ActiveDocument.Application.Range("D2").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "300";

    frm.WebOffice.ActiveDocument.Application.Range("B3").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "2";
    frm.WebOffice.ActiveDocument.Application.Range("C3").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "�Ǻ�";
    frm.WebOffice.ActiveDocument.Application.Range("D3").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "50";

    frm.WebOffice.ActiveDocument.Application.Range("B4").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "3";
    frm.WebOffice.ActiveDocument.Application.Range("C4").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "��ɫ";
    frm.WebOffice.ActiveDocument.Application.Range("D4").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "90";

    frm.WebOffice.ActiveDocument.Application.Range("B5").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "1";
    frm.WebOffice.ActiveDocument.Application.Range("C5").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "20cm";
    frm.WebOffice.ActiveDocument.Application.Range("D5").Select;
    frm.WebOffice.ActiveDocument.Application.ActiveCell.FormulaR1C1 = "40";

    //����������
    frm.WebOffice.ActiveDocument.Application.Range("B2:D5").Select;
    frm.WebOffice.ActiveDocument.Application.Selection.Locked = false;
    frm.WebOffice.ActiveDocument.Application.Selection.FormulaHidden = false;
    frm.WebOffice.ActiveDocument.Application.ActiveSheet.Protect(true, true, true);

    alert("�Ѿ����������?ֻ��B2-D5��Ԫ������޸ġ�");
}

//���ã���ȡ�ĵ�ҳ��
function WebDocumentPageCount() {
    var intPageTotal;
    intPageTotal = frm.WebOffice.ActiveDocument.Application.ActiveDocument.BuiltInDocumentProperties(14);
    alert("�ĵ�ҳ����" + intPageTotal);
}
function WebToolbar(boolvalue) {

    frm.WebOffice.Toolbars = boolvalue;

}
function WebTitlebar(boolvalue) {
    frm.WebOffice.Titlebar = boolvalue;
}
function WebInsertImage() {
    frm.WebOffice.ActiveDocument.Application.Selection.InlineShapes.AddPicture("http://www.superoa.net/login.gif", false, true);
}
