<div class="filePickerContainer">
	<div class="contentNavigation">
		<nav>
			<ul>
				<li class="dropdown filePickerCategoryDropdown">
					<a class="button dropdownToggle"> <span class="icon icon16 fa-sort"></span> <span>{lang}wcf.category.button.choose{/lang}</span></a>

					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$categoryList item=node}
								<li{if $node->categoryID == $category->categoryID} class="active"{/if} data-category-id="{@$node->categoryID}">
									<span>{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:$categoryList->getDepth()}{$node->getTitle()}</span>
								</li>
							{/foreach}
						</ul>
					</div>
				</li>

				{* check news image permission *}
				{if $__wcf->getSession()->getPermission('user.fireball.news.image.canUpload')}
					<li>
						<a class="button jsFileUploadButton"> <span class="icon icon16 fa-upload"></span> <span>{lang}cms.news.image.upload{/lang}</span> </a>
					</li>
				{/if}
				{event name='contentNavigationButtons'}
			</ul>
		</nav>
	</div>

	{include file='categoryFileListDialog' application='cms'}
</div>
