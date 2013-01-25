var clickItemobj,checkedstr = gid = "";
var checkedarr = new Array();
function clickNode(){
	this.obj = null;
	this.caption = null;
	this.level = null;
	this.value = null;
}
function STree(pParent,xmlFile){
	this.PICPATH = siteUrl+"/admin/images/tree/"	//ͼƬ�ļ����ڵ��ļ��У��ɼ�public���ɸı䡣
	
	var self = this;	//�൱��һ�����ã�ָ���Լ���STree.
	//-----------------------------------------------------------------------------
	//���ɼ�private��
	//����
	var JOIN = this.PICPATH +	"join.gif";
	var JOINBOTTOM = this.PICPATH +	"joinbottom.gif";
	var MINUS = this.PICPATH +	"minus.gif";
	var MINUSBOTTOM = this.PICPATH +	"minusbottom.gif";
	var PLUS = this.PICPATH +	"plus.gif";
	var PLUSBOTTOM = this.PICPATH +	"plusbottom.gif";
	var EMPTY = this.PICPATH +	"empty.gif";
	var LINE = this.PICPATH +	"line.gif";
	
	var LEAFICON = this.PICPATH +	"page.gif";
	var NODEICON = this.PICPATH + 	"folder.gif";
	var NODEOPEN = self.PICPATH + 	"folderopen.gif";
	
	var OPEN = new Array();
		OPEN[true] = MINUS;
		OPEN[false] = PLUS;
		
	var folder = new Array();
		folder[true] = NODEOPEN;
		folder[false] = NODEICON;
		
	var OPENBOTTOM = new Array();
		OPENBOTTOM[true] = MINUSBOTTOM;
		OPENBOTTOM[false] = PLUSBOTTOM;
	
	this.CAPTIONATT = "caption";//������������һ������
	this.ICONATT = "icon";//ͼ������
	this.EXPANDALL = true;//�Ƿ�ȫ����չ
	this.spread = "spread";//�ڵ��Ƿ�չ��:trueչ����false��չ��
	
	this.clickItem = new clickNode;//���ڵ��ʱ������ֵ��
	this.selectNode = null;//ͬ��
	
	this.onclick = null;
	this.onmouseover = null;
	this.onmouseout = null;

	this.body = getbyid(pParent) || document.body;
	//-----------------------------------------------------------------------------
	//��XML����
	var createXMLDom = function(){
		if (window.ActiveXObject) 
			var xmldoc = new ActiveXObject("Microsoft.XMLDOM");
		else 
			if (document.implementation && document.implementation.createDocument)
				var xmldoc = document.implementation.createDocument("","doc",null);
		xmldoc.async = false;
		//Ϊ�˺�FireFoxһ�������ﲻ�ܸ�ΪFalse;
		xmldoc.preserveWhiteSpace = true;
		return xmldoc;
	}
		
	//-----------------------------------------------------------------------------
	//����XML�ļ���
	var xmlDom = createXMLDom();
	if (window.ActiveXObject) {
		xmlDom.loadXML(xmlFile);
	} else {
		var vParser = new DOMParser();
		xmlDom = vParser.parseFromString(xmlFile, "text/xml");
		
	}
	//ע��FF��֧��xml
	var DOMRoot = xmlDom.documentElement;
	//ȡ��ָ���ڵ�����ԡ�
	var getDOMAtt = function(pNode,pAttribute){
		try{
			return pNode.attributes.getNamedItem(pAttribute).nodeValue;
		}catch(e){
			return false;
		}
		
	}
	//-----------------------------------------------------------------------------
	//�½�HTML��ǩ��
	var createTag = function(pTagName){
		return document.createElement(pTagName)
	}
	var createImg = function(pSrc){
		var tmp=createTag("IMG");
		tmp.align = "absmiddle";
		tmp.src = pSrc;
		tmp.onerror = function(){
			try{this.parentNode.removeChild(this);}catch(e){}
		}
		if(pParent=="containerLeft" && pSrc != false && (pSrc.indexOf('folder.gif') != -1 || pSrc.indexOf('folderopen.gif') != -1)) {
			tmp.onclick = function() {
				unsel(this.parentNode);
			}
		}
		return tmp;
	}
	var createCheckbox = function(pNode) {
		var tmp;
		try{
			tmp=createTag("<input name=\""+(pParent=="containerLeft" ? "Lblogid":"Rblogid")+"\"/>");
		} catch(e) {
			tmp=createTag("input");
			tmp.name = pParent=="containerLeft" ? "Lblogid":"Rblogid";
		}
		
		tmp.type = pParent=="containerLeft" ? "checkbox":"radio";
		tmp.setAttribute("name", pParent=="containerLeft" ? "Lblogid":"Rblogid",1);
		tmp.height = "15";
		tmp.value = getDOMAtt(pNode, 'itemid');
		tmp.id = pParent + getDOMAtt(pNode, 'itemid');
		tmp.parentNodeName = getDOMAtt(pNode, 'parent');
		tmp.caption = getDOMAtt(pNode, 'caption');
		tmp.style.height = "15px";
		tmp.style.backgroundImage = 'none';
		tmp.style.backgroundColor = 'transparent';
		tmp.style.borderWidth = '0';
		tmp.onclick = function() {
			if(this.checked)
				this.nextSibling.click();
		}
		tmp.onerror = function(){
			try{this.parentNode.removeChild(this);}catch(e){}
		}
		return tmp;
	}

	var createCaption = function(pNode,pLevel){
		var tmp = createTag("SPAN");
		tmp.innerHTML = getDOMAtt(pNode,self.CAPTIONATT);
		tmp.className = "caption";
		tmp.onmouseover = function(){
			if(this.className != "captionHighLight")
				this.className = "captionActive";
			try{self.onmouseover()}catch(e){}//�������
		}
		tmp.onmouseout = function(){
			if(this.className != "captionHighLight")
				this.className = "caption";
			try{self.onmouseout()}catch(e){}//�������
		}
		tmp.onclick = function() {
			try{
				self.clickItem.obj.className = "caption";
			}catch(e){
				//
			}
			this.className = "captionHighLight";
			preobj = this.previousSibling;
			if(preobj!=null && preobj.tagName.toLowerCase() == "input") {
				preobj.checked = true;
			}
			var clickItem = new clickNode;
			
			clickItem.obj = tmp;
			
			if(pParent == 'containerRight') {
				clickItemobj = tmp;
			}
			
			clickItem.caption = getDOMAtt(pNode,self.CAPTIONATT);
			clickItem.level = pLevel
			
			self.clickItem = clickItem;
			self.selectNode = pNode;
			try{self.onclick();}catch(e){}//������ϣ����selfû�ж�onclick��ֵ�Ļ�������������
		}
		return tmp;
	}

	var createTreeLine = function(pNode,pParentArea){
		var hasChildren = pNode.hasChildNodes();//�Ƿ��к��ӡ�		
		for(var i=0; i<pParentArea.level; i++){
			var tmpArea = pParentArea;		
			for(var j=pParentArea.level; j>i; j--){
				tmpArea = tmpArea.parentNode.parentNode;
			}
			
			if(tmpArea.isLastChild)
				appendTo(createImg(EMPTY),pParentArea);
			else
				appendTo(createImg(LINE),pParentArea);
		}
				
		if(hasChildren){//�к���
			var childShowBtn;
			var tmpSpread = getDOMAtt(pNode,self.spread);
			if(!pParentArea.isLastChild){	
				childShowBtn = createImg(OPEN[tmpSpread]);	//����һ���������Ĳ��ͼ��
				appendTo(childShowBtn,pParentArea);
			}else{
				childShowBtn = createImg(OPENBOTTOM[tmpSpread]);	//����һ�������Ĳ��ͼ��
				appendTo(childShowBtn,pParentArea);
			}
			childShowBtn.onclick = function(){
				var isExpand=this.parentNode.expand();
				this.nextSibling.src = folder[isExpand];	//��ȡ��һ�����ڵĽڵ�
				if(!pParentArea.isLastChild){
					this.src = OPEN[isExpand];
				}else{
					this.src = OPENBOTTOM[isExpand];
				}
			}
		}else{//�޺��ӡ�
			if(!pParentArea.isLastChild)	
				appendTo(createImg(JOIN),pParentArea);
			else
				appendTo(createImg(JOINBOTTOM),pParentArea);			
		}
	}
	
	var createIcon = function(pNode,pParentArea){
		var hasChildren = pNode.hasChildNodes();//�Ƿ��к���
		var tmpIcon = getDOMAtt(pNode,self.ICONATT);	//��ȡ�ڵ����ԣ��ж��Ƿ���Ԥ�����ͼ��
		var tmpSpread = getDOMAtt(pNode,self.spread);	//��ȡ�Ƿ�չ������ֵ
		if(tmpIcon == false){
			if(hasChildren)
				appendTo(createImg(folder[tmpSpread]),pParentArea);
			else
				appendTo(createCheckbox(pNode),pParentArea);
		}else{
			appendTo(createImg(tmpIcon),pParentArea);
		}		
	}
	//-----------------------------------------------------------------------------
	//��ָ��OBJ׷�ӵ�ĳ��OBJ������档
	var appendTo = function(pObj,pTargetObj){
		try{
			pTargetObj.appendChild(pObj);
		}catch(e){
			alert(e.message);
		}
	}
	//-----------------------------------------------------------------------------
	var isFirstChild = function(pNode){
		//���˿հ׽ڵ�֮�⣬�Ƿ��ǵ�һ���ڵ�
		var tmpNode = pNode.previousSibling;
		while(tmpNode != null && tmpNode.previousSibling != null && tmpNode.nodeType != 1)
			tmpNode=tmpNode.previousSibling;
		if(tmpNode == null || tmpNode.nodeType == 3)//�ǿսڵ�
			return true;
		else
			return false;
	}
	var isLastChild = function(pNode){
		tmpNode=pNode.nextSibling;
		while(tmpNode != null && tmpNode.nextSibling != null && tmpNode.nodeType != 1)
			tmpNode=tmpNode.nextSibling;
		if(tmpNode == null || tmpNode.nodeType == 3)//�ǿսڵ�
			return true;
		else
			return false;		
	}
	//-----------------------------------------------------------------------------
	//ѭ�����Ƹ��ڵ㡣��������Щ����Щ�ڵ�����������ܣ����ԣ��������Щ��Ӧ�ñ�oRoot����������Ӧ����oOutLine�ĺ��ӡ�
	var createSubTree = function(pNode,pLevel,pNodeArea){
		var subNode;
		for(var i=0; subNode=pNode.childNodes[i]; i++){
			if(subNode.nodeType != 1) continue;//����Ĭ���˰ѿհ�Ҳ����һ���ڵ����������ԣ�����Ҫ�ж�һ�¡�
			
			var subNodeItem = createTag("DIV")
			
			if(subNode.hasChildNodes()){
				var subNodeSubArea = createTag("DIV");
				var tmpSpread = getDOMAtt(subNode,self.spread);
//				subNodeSubArea.style.wordWrap = "normal";
				//����ȡ�ڵ�ķ���ֵΪ�ַ���������������ַ�����һ��ת������
				if(typeof tmpSpread == 'string') {
					var re = new RegExp("false","ig");
					tmpSpread = re.test(tmpSpread)?false:true;
				}
				if(!tmpSpread)
					subNodeSubArea.style.display = 'none';
			}
			subNodeItem.level = pLevel+1;
			subNodeItem.isFirstChild = isFirstChild(subNode);
			subNodeItem.isLastChild	= isLastChild(subNode);
			subNodeItem.itemid	= getDOMAtt(subNode,'itemid');
			subNodeItem.id = getDOMAtt(subNode,'id');
			subNodeItem.style.whiteSpace = "nowrap";
			//��������λ�ò��ܱ䶯����ΪcreateTreeLine���õ�������parentNode
			appendTo(subNodeItem,pNodeArea);
			
			createTreeLine(subNode,subNodeItem);
			createIcon(subNode,subNodeItem);
			var subNodeCaption = createCaption(subNode,pLevel+1);
			appendTo(subNodeCaption,subNodeItem);

			if(subNode.hasChildNodes()) {
				//createSubTree(subNode,pLevel+1,subNodeItem);
				appendTo(subNodeSubArea,subNodeItem);
				createSubTree(subNode,pLevel+1,subNodeSubArea);
				subNodeItem.subNodeSubArea = subNodeSubArea;
				
				subNodeItem.expand = function(){
					//���״̬��չ���������棬���򷵻ؼ١�
					if(this.subNodeSubArea.style.display == ""){
						this.subNodeSubArea.style.display = "none";
						return false;
					}else{
						this.subNodeSubArea.style.display = "";
						return true;	
					}
				};
			}
		}
	}
	
	
	this.expandByLevel = function(pLevel){
			
	}
	
	this.create = function(){
		//-----------------------------------------------------------------------------
		//��������
		var oOutLine = createTag("DIV");
		oOutLine.className = "outLine";
		appendTo(oOutLine,this.body);
		//-----------------------------------------------------------------------------
		//���Ƹ�����������߱������Ĺ��ܡ�
		var oRoot = createTag("DIV");
		oRoot.id = 0;
		oRoot.level	=-1;//���𡣸��ļ���Ϊ-1;
	
		var oRootIcon =createImg(getDOMAtt(DOMRoot,self.ICONATT));	
		var oRootCaption=createCaption(DOMRoot,-1);
		appendTo(oRootIcon,oRoot);
		appendTo(oRootCaption,oRoot);
		appendTo(oRoot,oOutLine);
		//------------------------------------------------------------------------------
		
		createSubTree(DOMRoot,-1,oOutLine);
	}
}
function getNodeMaxIdaa(statnodes) {
	var nid,aid = 1;
	for(k=0; k< statnodes.childNodes.length; k++) {
		nid = statnodes.childNodes[k].getAttribute("id");
		if(nid.lastIndexOf(".") != -1) {
			nid = nid.substring(nid.lastIndexOf(".")+1);
		}
		if(aid < parseInt(nid)) {
			aid = parseInt(nid);
		}
	}
	return aid;
}
/**
 * ����Ŀ¼���������б�
 * @param array blogarr:�������������
 */
function addNode(blogarr) {
	//��ȡĿ¼������
	var itemid,caption;
	itemid = typeof blogarr != 'undefined' && blogarr['itemid'] != null ? blogarr['itemid']:0;
	caption = typeof blogarr != 'undefined' && blogarr['caption'] != null ? blogarr['caption']:null;
	var xmlDom = createxmlDom(rtreexml);
	var firstNode = false, newNode, selectnode = 0;
	var lastid = 0;
	var levelvalue,nodevalue,dialog;
	if(!xmlDom.childNodes[0].hasChildNodes() || clickItemobj == null || clickItemobj.parentNode.level == -1) {
		firstNode = true;
		gid = "";
		newNode = xmlDom.createNode(1, "level1", "");
		if(caption == null) {
			dialog = getdialog();
			if(dialog == false) {
				return false;
			}
		} else {
			dialog = caption;
		}
		if(xmlDom.childNodes[0].lastChild != null) {
			lastid = xmlDom.childNodes[0].lastChild.getAttribute("id");
		}
		newNode.setAttribute('caption', dialog);
		newNode.setAttribute('itemid', itemid);
		newNode.setAttribute('spread', 'true');
		newNode.setAttribute('id', parseInt(lastid)+1);
		xmlDom.childNodes[0].appendChild(newNode);

	} else {
		
		//�ж�ѡ����Ƿ�Ϊ��Ŀ¼
		levelvalue = parseInt(clickItemobj.parentNode.level);
		gid = nodevalue = clickItemobj.parentNode.id;
		if(clickItemobj.parentNode.itemid != 0) {
			alert("���������½ڵ��ϴ����ӽڵ���������");
			return false;
		}
		var nowNode = xmlDom.selectNodes("//level" + (levelvalue+1) +"[@id='"+ nodevalue +"']")[0];
		
		newNode = xmlDom.createNode(1, "level"+(levelvalue+2), "");
		if(caption == null) {
			dialog = getdialog();
			if(dialog == false) {
				return false;
			}
		} else {
			dialog = caption;
		}
		//������һ���ڵ��ID
		if(nowNode.lastChild != null) {
			lastid = nowNode.lastChild.getAttribute('id');
			if(lastid.lastIndexOf('.') != -1) {
				lastid = parseInt(lastid.substring(lastid.lastIndexOf('.')+1));
			}
		}
		newNode.setAttribute('caption',dialog);
		newNode.setAttribute('itemid',itemid);
		newNode.setAttribute('spread','true');
		newNode.setAttribute('id',nodevalue+"."+(parseInt(lastid)+1));
		nowNode.appendChild(newNode);
	}

	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	//���¼���ԭ���Ĳ����ڵ�
	if(gid != "") {
		getbyid(gid).getElementsByTagName("span")[0].click();
		gid = "";
	}
	return true;

}

/**
 * ������Ƴ�ָ���Ľڵ�
 */
function delNode() {
	if(clickItemobj == null) return false;
	var levelvalue = parseInt(clickItemobj.parentNode.level);
	if(levelvalue == -1) levelvalue = 0;
	nodevalue = clickItemobj.parentNode.id;
	var xmlDom = createxmlDom(rtreexml);
	var term = "//level" + (levelvalue+1);
	if(nodevalue != "" && nodevalue != "0") {
		term += "[@id='"+ nodevalue +"']";
	}
	var currNode = xmlDom.selectNodes(term);
	for(i=0; i<currNode.length; i++) {
		currNode[i].parentNode.removeChild(currNode[i]);
		//���¼�����ߵ�blogѡ�ֿ�
		
		var checkboxobj, itemid = currNode[i].attributes.getNamedItem("itemid").nodeValue;
		if(itemid>0) {
			checkboxobj = getbyid("containerLeft"+itemid);
			checkboxobj.disabled = false;
			checkboxobj.checked = false;
			//�Ƴ�ѡ����
			getbyid("theform").removeChild(getbyid('bid'+itemid));
		} else {
			var j = 0,nodenum = currNode[i].selectNodes("//");
			for(j=0; j<nodenum.length; j++) {
				itemid = nodenum[j].attributes.getNamedItem("itemid").nodeValue;
				if(itemid>0) {
					checkboxobj = getbyid("containerLeft"+itemid);
					checkboxobj.disabled = false;
					checkboxobj.checked = false;
					//�Ƴ�ѡ����
					getbyid("theform").removeChild(getbyid('bid'+itemid));
				}
			}
		}
	}
	//�ع���
	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	gid="";
}
/**
 * �����ڵ�
 */
function exchangeNode(op) {
	var opitemid,opvalue;
	if(clickItemobj == null) return false;
	var levelvalue = parseInt(clickItemobj.parentNode.level);
	if(levelvalue == -1) levelvalue = 0;
	nodevalue = clickItemobj.parentNode.id;
	var xmlDom = createxmlDom(rtreexml);
	var term = "//level" + (levelvalue+1);
	if(nodevalue != "") {
		term += "[@id='"+ nodevalue +"']";
	}
	var currNode = xmlDom.selectNodes(term)[0];
	opvalue = currNode.getAttribute("caption");
	opitemid = currNode.getAttribute("itemid");
	var pNode = currNode.parentNode;
	var index,exNode;
	for(i=0; i<pNode.childNodes.length; i++) {
		if(nodevalue == pNode.childNodes[i].getAttribute("id")) {
			index = i;
			break;
		}
	}
	if(op == "up") {
		exNode = pNode.childNodes[index].previousSibling;
		
	} else if(op == "down") {
		exNode = pNode.childNodes[index].nextSibling;
	}
	if(exNode != null) {
		gid = currNode.getAttribute("id");
		pNode.replaceChild(exNode.cloneNode(true), currNode);
		pNode.replaceChild(currNode,exNode);
	}
	//�ع���
	getbyid("rxml").value = rtreexml = xmlDom.xml;
	getbyid('containerRight').innerHTML = "";
	leftTree = new STree("containerRight", rtreexml);
	leftTree.CAPTIONATT="caption";
	leftTree.create();
	clickItemobj = null;
	if(gid != "")
		getbyid(""+gid+"").getElementsByTagName("span")[0].click();
}
/**
 * �����˻��Ự�������ػỰ���
 */
function getdialog(initialization) {
	if(initialization == null) initialization="";
	var dialog = window.prompt("�������½�����", initialization);
	if(dialog == null) return false;
	return dialog;
}
/**
 * ���½��������
 */
function addarticle() {
	//��ȡ�������ѡ���
	var inputobj = '';
	var newinputobj, cls = true;
	checkedarr = replacecomma(checkedstr).split(',');
	if(checkedarr.length > 0) {
		if(checkedarr.length > 20) {
			if(confirm('��һ�����ƶ���20���ڵ㣬�ͻ��˻��е�����ȷ���ƶ���')==false) {
				return false;
			}
		}
		for (key in checkedarr) {
			if(checkedarr[key] != 0) {
				inputobj = getbyid(checkedarr[key]);
				if(inputobj.checked && !inputobj.disabled) {
					//�������
					var blogarr = {caption:inputobj.caption,itemid:inputobj.value};
					if(addNode(blogarr)) {
						try{
							newinputobj = document.createElement("<input name='blogsid[]'/>"); 
						}catch(e) {
							newinputobj = document.createElement("input");
							newinputobj.setAttribute('name', 'blogsid[]');
						}
						newinputobj.setAttribute('type', 'hidden');
						newinputobj.setAttribute('id', "bid"+inputobj.value);
						newinputobj.setAttribute('value', inputobj.value);
						getbyid("theform").appendChild(newinputobj);
						inputobj.disabled = true;
					} else {
						cls = false;
						break;
					}
				}
			} else {
				continue;
			}
		}
		if(cls) {
			checkedarr = new Array();
			checkedstr = "";
		}
	}
}

/**
 * �������ڵ�����
 */
function renameNode() {
	if(clickItemobj == null) { 
		alert('��ѡ��Ҫ���������½ڵ㣬���ܶ����±������������');
		return false;
	} else {
		var levelvalue = parseInt(clickItemobj.parentNode.level);
		if(levelvalue == -1) levelvalue = 0;
		nodevalue = clickItemobj.parentNode.id;
		var xmlDom = createxmlDom(rtreexml);
		var term = "//level" + (levelvalue+1);
		if(nodevalue != "") {
			term += "[@id='"+ nodevalue +"']";
		}
		var currNode = xmlDom.selectNodes(term)[0];
		dialog = getdialog(currNode.getAttribute("caption"));
		if(dialog == false) {
			return false;
		}
		currNode.setAttribute('caption', dialog);
		//�ع���
		getbyid("rxml").value = rtreexml = xmlDom.xml;
		getbyid('containerRight').innerHTML = "";
		leftTree = new STree("containerRight", rtreexml);
		leftTree.CAPTIONATT="caption";
		leftTree.create();
		clickItemobj = null;
	}
}
function allsel(obj) {
	var selobj = obj.getElementsByTagName("input");
	for(i=0; i<selobj.length; i++) {
		if(selobj[i].type == 'checkbox' && !selobj[i].disabled) {
			selobj[i].checked = true;
			opcheckedarr(selobj[i]);
		}
	}
}

function unsel(obj) {
	var selobj = obj.getElementsByTagName("input");
	for(i=0; i<selobj.length; i++) {
		if(selobj[i].type == 'checkbox' && !selobj[i].disabled) {
			selobj[i].checked = !selobj[i].checked;
			opcheckedarr(selobj[i]);
		}
	}
}
function createxmlDom(xml) {
	if(window.ActiveXObject) {
		vXMLDoc = new ActiveXObject("Microsoft.XMLDOM");
		vXMLDoc.async = false;
		vXMLDoc.loadXML(xml);
	}else if(document.implementation.createDocument) {
		var vParser = new DOMParser();
		vXMLDoc = vParser.parseFromString(xml, "text/xml");
	}
	return vXMLDoc;
}
function opcheckedarr(obj){
	if(obj.checked) {
		checkedstr +=  ',' + obj.id;
	} else {
		var re = new RegExp(obj.id, "ig");
		checkedstr = checkedstr.replace(re,'');
		checkedstr = replacecomma(checkedstr);
	}
}
function replacecomma(str) {
	str = str.replace(/,+/ig, ',');
	str = str.replace(/^,+/ig, '');
	str = str.replace(/,+$/ig, '');
	return str;
}
function opexpand(obj) {
	this.PICPATH = siteUrl+"/admin/images/tree/"	//ͼƬ�ļ����ڵ��ļ��У��ɼ�public���ɸı䡣
	//����
	var MINUS = this.PICPATH +	"minus.gif";
	var MINUSBOTTOM = this.PICPATH +	"minusBottom.gif";
	var PLUS = this.PICPATH +	"plus.gif";
	var PLUSBOTTOM = this.PICPATH +	"plusBottom.gif";

	var NODEICON = this.PICPATH + 	"folder.gif";
	var NODEOPEN = self.PICPATH + 	"folderopen.gif";
	
	var OPEN = new Array();
		OPEN[true] = MINUS;
		OPEN[false] = PLUS;
		
	var folder = new Array();
		folder[true] = NODEOPEN;
		folder[false] = NODEICON;
		
	var OPENBOTTOM = new Array();
		OPENBOTTOM[true] = MINUSBOTTOM;
		OPENBOTTOM[false] = PLUSBOTTOM;
		
	var eobj = obj.parentNode.lastChild;
	var isopen;
	if(eobj.style.display == ""){
		eobj.style.display = "none";
		isopen = false;
	} else {
		eobj.style.display = "";
		isopen = true;
	}
	if(obj.src.indexOf('Bottom')!=-1) {
		obj.src = OPENBOTTOM[isopen];
	} else {
		obj.src = OPEN[isopen];
	}
	obj.nextSibling.src = folder[isopen];	
}